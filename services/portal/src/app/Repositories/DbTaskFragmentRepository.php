<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\SearchableHash;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Models\Task\PersonalDetails;
use App\Schema\Fragment;
use App\Services\TaskFragmentService;
use DateTimeImmutable;
use DBCO\Shared\Application\Helpers\PhoneFormatter;
use Exception;
use MinVWS\Codable\JSONDecoder;
use MinVWS\Codable\JSONEncoder;
use MinVWS\DBCO\Encryption\Security\EncryptionHelper;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use Throwable;

use function preg_replace_callback;
use function strtolower;

class DbTaskFragmentRepository implements TaskFragmentRepository
{
    private EncryptionHelper $encryptionHelper;
    private SearchableHash $searchableHash;

    public function __construct(EncryptionHelper $encryptionHelper, SearchableHash $searchableHash)
    {
        $this->encryptionHelper = $encryptionHelper;
        $this->searchableHash = $searchableHash;
    }

    private function columnNameForFragmentName(string $fragmentName): string
    {
        /**
         * verb1Verb2 => verb1_verb2
         *
         * @var string $columnName
         */
        $columnName = preg_replace_callback('/[A-Z]/', static fn ($m) => '_' . strtolower($m[0]), $fragmentName);

        return $columnName;
    }

    /**
     * @inheritDoc
     */
    public function loadTaskFragments(string $taskUuid, array $fragmentNames, bool $includingSoftDeletes = false): array
    {
        $task = EloquentTask::query()
            ->when($includingSoftDeletes, static function ($query): void {
                $query->withTrashed();
            })
            ->find($taskUuid);

        if ($task === null) {
            throw new Exception("Task not found");
        }

        $fragmentClasses = TaskFragmentService::fragmentClasses();
        $decoder = new JSONDecoder();

        $result = [];
        foreach ($fragmentNames as $fragmentName) {
            $fragmentClass = $fragmentClasses[$fragmentName];
            $result[$fragmentName] = $this->loadTaskFragment($task, $fragmentName, $fragmentClass, $decoder);
        }

        return $result;
    }

    /**
     * @param class-string $fragmentClass
     *
     * @throws Exception
     */
    private function loadTaskFragment(EloquentTask $task, string $fragmentName, string $fragmentClass, JSONDecoder $decoder): object
    {
        try {
            $columnName = $this->columnNameForFragmentName($fragmentName);

            $fragment = $task->$columnName ?? null;

            if (!$fragment instanceof Fragment) {
                $json = $this->encryptionHelper->unsealOptionalStoreValue($fragment);

                $fragment = $json === null ? new $fragmentClass() : $decoder->decode($json)->decodeObject($fragmentClass);
            }

            $this->postLoadTaskFragment($task, $fragment);

            return $fragment;
        } catch (Throwable $e) {
            // should not happen, so make it a runtime exception so that if it occurs we
            // return a 501 and the error gets logged
            throw new Exception($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * @inheritDoc
     */
    public function storeTaskFragments(string $taskUuid, array $fragments): void
    {
        $task = null;
        foreach ($fragments as $fragment) {
            if ($fragment instanceof Fragment && $fragment->getOwner() !== null) {
                $task = $fragment->getOwner();
                break;
            }
        }

        if (!$task instanceof EloquentTask) {
            $task = EloquentTask::find($taskUuid);
        }

        if (!$task instanceof EloquentTask) {
            throw new Exception("Task not found");
        }

        $encoder = new JSONEncoder();
        foreach ($fragments as $fragmentName => $fragment) {
            $this->preStoreTaskFragment($task, $fragment);

            $columnName = $this->columnNameForFragmentName((string) $fragmentName);

            if ($fragment instanceof Fragment) {
                $task->$columnName = $fragment;
            } else {
                $json = $encoder->encode($fragment);
                $task->$columnName = $this->encryptionHelper->sealStoreValue(
                    $json,
                    StorageTerm::short(),
                    $task->created_at,
                );
            }

            if ($fragment instanceof General && $fragment->phone !== null) {
                $fragment->phone = PhoneFormatter::format($fragment->phone);
            }
        }

        if (!$task->save()) {
            throw new Exception("Unable to store task fragments");
        }
    }

    /**
     * Set data based on the main entity.
     */
    private function postLoadTaskFragment(EloquentTask $task, object $fragment): void
    {
        if ($fragment instanceof PersonalDetails) {
            $this->postLoadPersonalDetailsFragment($task, $fragment);
        }
    }

    /**
     * Inject additional data for the personal details fragment.
     *
     * @throws Exception
     */
    private function postLoadPersonalDetailsFragment(EloquentTask $task, PersonalDetails $fragment): void
    {
        $this->loadPersonalDetailsDataFromQuestionnaire($task, $fragment);
    }

    /**
     * Load some personal details from the questionnaire.
     *
     * @throws Exception
     */
    private function loadPersonalDetailsDataFromQuestionnaire(EloquentTask $task, PersonalDetails $fragment): void
    {
        if (!empty($fragment->dateOfBirth)) {
            return;
        }

        $birthDateDetails = $task->birthDateAnswer();
        if ($birthDateDetails === null) {
            return;
        }

        $unsealedDateOfBirth = $this->encryptionHelper->unsealOptionalStoreValue($birthDateDetails->spv_value);
        $fragment->dateOfBirth = $unsealedDateOfBirth !== null ? new DateTimeImmutable($unsealedDateOfBirth) : null;
    }

    /**
     * Update main entity based on fragment.
     *
     * @throws Exception
     */
    private function preStoreTaskFragment(EloquentTask $task, object $fragment): void
    {
        if ($fragment instanceof PersonalDetails) {
            $fragments = $this->loadTaskFragments($task->uuid, ['general']);
            /** @var General $general */
            $general = $fragments['general'];

            if ($general->lastname && $fragment->dateOfBirth && !empty($general->lastname) && !empty($fragment->dateOfBirth)) {
                $task->search_date_of_birth = $this->searchableHash->hashForLastNameAndDateOfBirth(
                    $general->lastname,
                    $fragment->dateOfBirth,
                );
            }
        }
    }
}
