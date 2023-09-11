<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\FragmentNotAccessibleException;
use App\Models\Eloquent\EloquentBaseModel;
use App\Models\Eloquent\EloquentTask;
use App\Repositories\TaskFragmentRepository;
use App\Repositories\TaskRepository;
use App\Services\Task\TaskDecryptableDefiner;
use DBCO\Shared\Application\Metrics\Events\AbstractEvent;
use MinVWS\DBCO\Metrics\Services\EventService;
use Webmozart\Assert\Assert;

use function config;

class TaskFragmentService extends AbstractFragmentService
{
    private const FRAGMENT_NAMES = [
        'general',
        'inform',
        'alternativeLanguage',
        'circumstances',
        'symptoms',
        'test',
        'vaccination',
        'personalDetails',
        'alternateContact',
        'job',
        'immunity',
    ];

    public function __construct(
        private readonly TaskFragmentRepository $taskFragmentRepository,
        private readonly TaskRepository $taskRepository,
        private readonly TaskDecryptableDefiner $taskDecryptableDefiner,
        private readonly EventService $eventService,
    ) {
    }

    protected static function fragmentNamespace(): string
    {
        return 'App\Models\Task';
    }

    /**
     * @inheritDoc
     */
    public static function fragmentNames(): array
    {
        return self::FRAGMENT_NAMES;
    }

    /**
     * @inheritDoc
     */
    public function loadFragments(string $ownerUuid, array $fragmentNames, bool $includeSoftDeletes = false): array
    {
        $this->assertAccessibleTask($ownerUuid);

        return $this->taskFragmentRepository->loadTaskFragments($ownerUuid, $fragmentNames, $includeSoftDeletes);
    }

    /**
     * @inheritDoc
     */
    public function storeFragments(string $ownerUuid, array $fragments): void
    {
        $this->assertAccessibleTask($ownerUuid);

        $oldTaskData = $this->eventService->retrieveTaskData($ownerUuid);

        $this->taskFragmentRepository->storeTaskFragments($ownerUuid, $fragments);

        $this->eventService->registerTaskMetrics(AbstractEvent::ACTOR_STAFF, $oldTaskData['case_uuid'], $oldTaskData['uuid'], $oldTaskData);
    }

    /**
     * @inheritDoc
     */
    protected function getAdditionalValidationData(EloquentBaseModel $owner, array $fragmentData): array
    {
        Assert::isInstanceOf($owner, EloquentTask::class);

        if (!empty($this->cachedAdditionalValidationData)) {
            return $this->cachedAdditionalValidationData;
        }

        $this->cachedAdditionalValidationData['task'] = $owner;

        $covidCreatedAt = $owner->covidCase->created_at;

        $this->cachedAdditionalValidationData['caseCreationDate'] = $covidCreatedAt->format('Y-m-d');
        $this->cachedAdditionalValidationData['maxBeforeCaseCreationDate'] = $covidCreatedAt->copy()
            ->sub(config('misc.validations.maxBeforeCaseCreationDateInDays') . ' days')->format('Y-m-d');

        return $this->cachedAdditionalValidationData;
    }

    /**
     * @throws FragmentNotAccessibleException
     */
    private function assertAccessibleTask(string $ownerUuid): void
    {
        $task = $this->taskRepository->getTaskModelIncludingSoftDeletes($ownerUuid);

        if ($task === null) {
            return;
        }

        if (!$this->taskDecryptableDefiner->isDecryptable($task)) {
            throw new FragmentNotAccessibleException();
        }
    }
}
