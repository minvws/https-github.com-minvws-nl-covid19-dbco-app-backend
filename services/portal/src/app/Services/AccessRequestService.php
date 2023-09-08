<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\CallToActionHistory\CallToActionHistoryDto;
use App\Dto\CallToActionHistory\EventDto;
use App\Exceptions\FragmentNotAccessibleException;
use App\Helpers\TimezoneAware;
use App\Models\CovidCase\Codables\ExportEncoder;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Enums\Codables\EnumEncoder;
use App\Repositories\DbTaskRepository;
use App\Services\Chores\CallToActionHistoryService;
use App\Services\Task\TaskEncoder;
use Barryvdh\DomPDF\Facade\Pdf as PdfFacade;
use Barryvdh\DomPDF\PDF;
use Carbon\CarbonImmutable;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\Enum;

use function collect;
use function compact;
use function config;
use function in_array;
use function is_string;
use function preg_match;
use function sprintf;
use function view;

class AccessRequestService
{
    public const DAYS = 30;

    public function __construct(
        private readonly CaseFragmentService $caseFragmentService,
        private readonly ContextFragmentService $contextFragmentService,
        private readonly ContextService $contextService,
        private readonly TaskFragmentService $taskFragmentService,
        private readonly DbTaskRepository $dbTaskRepository,
        private readonly TaskEncoder $taskEncoder,
        private readonly CallToActionHistoryService $callToActionHistoryService,
    ) {
    }

    public function prepareCaseDownloadPdf(EloquentCase $case, string $name): PDF
    {
        $fragments = $this->collectCaseFragmentData($case);
        $contacts = $this->collectCaseContactData($case);
        $contexts = $this->collectContextData($case);
        $callToActions = $this->collectCallToActionData($case);

        return PdfFacade::loadView('pdf.case', compact('fragments', 'contacts', 'callToActions', 'contexts', 'name'));
    }

    public function prepareCaseDownloadHtml(EloquentCase $case, string $name): array|string
    {
        $fragments = $this->collectCaseFragmentData($case);
        $contacts = $this->collectCaseContactData($case);
        $contexts = $this->collectContextData($case);
        $callToActions = $this->collectCallToActionData($case);

        return view('pdf.case', compact('fragments', 'contacts', 'contexts', 'callToActions', 'name'))->render();
    }

    public function prepareTaskDownloadPdf(EloquentTask $task, string $name): PDF
    {
        $fragments = $this->collectTaskFragmentData($task);

        return PdfFacade::loadView('pdf.task', compact('fragments', 'name'));
    }

    public function prepareTaskDownloadHtml(EloquentTask $task, string $name): array|string
    {
        $fragments = $this->collectTaskFragmentData($task);

        return view('pdf.task', compact('fragments', 'name'))->render();
    }

    public function collectCaseFragmentData(EloquentCase $case): Collection
    {
        $fragments = $this->caseFragmentService->loadFragments($case->uuid, CaseFragmentService::fragmentNames());

        $data = $this->extractDataFromCaseFragments($fragments);
        return $this->postProcessData($data);
    }

    private function postProcessData(Collection $data): Collection
    {
        return $data->map(fn ($value) => $this->postProcessObject($value));
    }

    private function postProcessObject(object $data): object
    {
        $timestampFields = ['createdAt', 'deletedAt'];

        foreach ($data as $key => &$value) {
            if (
                is_string($value)
                && in_array($key, $timestampFields, true)
                && preg_match('/^(\d\d\d\d-\d\d-\d\dT\d\d:\d\d:\d\dZ)$/', $value) > 0
            ) {
                $value = TimezoneAware::format(new CarbonImmutable($value), 'Y-m-d H:i:s');
            }
        }

        return $data;
    }

    public function collectTaskFragmentData(EloquentTask $task): Collection
    {
        $fragments = $this->taskFragmentService->loadFragments($task->uuid, TaskFragmentService::fragmentNames());

        return $this->extractDataFromTaskFragments($fragments);
    }

    public function collectCaseContactData(EloquentCase $eloquentCase): Collection
    {
        $contacts = collect();

        foreach ($eloquentCase->getGroupedTasks() as $type) {
            /** @var EloquentTask $eloquentTask */
            foreach ($type as $eloquentTask) {
                try {
                    $fragments = $this->taskFragmentService->loadFragments($eloquentTask->uuid, [
                        'general',
                        'circumstances',
                    ]);
                    $contacts->push($this->extractDataFromTaskFragments($fragments));
                } catch (FragmentNotAccessibleException $exception) {
                    $task = $this->dbTaskRepository->getTask($eloquentTask->uuid);

                    if ($task === null) {
                        continue;
                    }

                    $encodedTask = collect($this->taskEncoder->encode($task))->only([
                        'dateOfLastExposure',
                        'category',
                        'isSource',
                        'label',
                        'nature',
                        'deletedAt',
                    ])->merge([
                        'availabilityNote' => sprintf(
                            'automatisch verwijderd na %s dagen',
                            config('misc.encryption.task_availability_in_days'),
                        )]);

                    $contacts->push(collect(['general' => $encodedTask]));
                }
            }
        }

        return $contacts;
    }

    public function collectContextData(EloquentCase $eloquentCase): Collection
    {
        $caseContexts = $this->contextService->getContextsForCase($eloquentCase);
        $contexts = collect();

        /** @var Context $context */
        foreach ($caseContexts as $context) {
            $fragments = $this->contextFragmentService->loadFragments(
                $context->uuid,
                ContextFragmentService::fragmentNames(),
            );

            $contexts->push($this->extractDataFromContextFragments($fragments));
        }

        return $contexts;
    }

    public function collectCallToActionData(EloquentCase $eloquentCase): Collection
    {
        /** @var Collection<int, CallToActionHistoryDto> $callToActionHistory */
        $callToActionHistory = $this->callToActionHistoryService->getCallToActionHistoryForCase($eloquentCase);

        return $callToActionHistory->map(static function (CallToActionHistoryDto $callToActionHistoryDto): Collection {
            /** @var Collection<int, EventDto> $events */
            $events = $callToActionHistoryDto->events;

            return new Collection([
                'createdAt' => $callToActionHistoryDto->createdAt,
                'expiresAt' => $callToActionHistoryDto->expiresAt,
                'deletedAt' => $callToActionHistoryDto->deletedAt,
                'subject' => $callToActionHistoryDto->subject,
                'description' => $callToActionHistoryDto->description,
                'userRoles' => $callToActionHistoryDto->userRoles,
                'events' => $events->map(static function (EventDto $event): Collection {
                    return new Collection([
                        'datetime' => ($event->dateTime ?? new DateTime())->format('Y-m-d H:i:s'),
                        'callToActionEvent' => $event->callToActionEvent->label,
                        'note' => $event->note,
                    ]);
                }),
            ]);
        });
    }

    private function extractDataFromCaseFragments(array $fragments): Collection
    {
        return $this->extractDataFromFragments($fragments, '\\App\\Models\\CovidCase\\Codables\\%sEncoder');
    }

    private function extractDataFromTaskFragments(array $fragments): Collection
    {
        return $this->extractDataFromFragments($fragments, '\\App\\Models\\Task\\Codables\\%sEncoder');
    }

    private function extractDataFromContextFragments(array $fragments): Collection
    {
        return $this->extractDataFromFragments($fragments, '\\App\\Models\\Context\\Codables\\%sEncoder');
    }

    private function extractDataFromFragments(array $fragments, string $encoderNamespace): Collection
    {
        $data = collect();

        foreach ($fragments as $key => $fragment) {
            $encoder = new ExportEncoder();
            $decoratorClass = sprintf($encoderNamespace, Str::studly((string) $key));

            $encoder->getContext()->registerDecorator($fragment::class, new $decoratorClass());
            $encoder->getContext()->registerDecorator(Enum::class, EnumEncoder::class);

            $data->put($key, $encoder->encode($fragment));
        }

        return $data;
    }
}
