<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Requests\SearchRequest;
use App\Http\Responses\EncodableResponse;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task\General;
use App\Models\ValueObjects\CaseIdentifier;
use App\Services\AuthenticationService;
use App\Services\CaseSearchService;
use App\Services\CaseService;
use App\Services\TaskFragmentService;
use App\Services\TaskSearchService;
use DateTimeInterface;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;

use function abort;
use function implode;
use function is_string;
use function sprintf;

class ApiSearchController extends ApiController
{
    private AuditService $auditService;
    private CaseSearchService $caseSearchService;
    private CaseService $caseService;
    private TaskFragmentService $taskFragmentService;
    private TaskSearchService $taskSearchService;
    private AuthenticationService $authenticationService;

    public function __construct(
        AuditService $auditService,
        CaseSearchService $caseSearchService,
        CaseService $caseService,
        TaskFragmentService $taskFragmentService,
        TaskSearchService $taskSearchService,
        AuthenticationService $authenticationService,
    ) {
        $this->auditService = $auditService;
        $this->caseSearchService = $caseSearchService;
        $this->caseService = $caseService;
        $this->taskFragmentService = $taskFragmentService;
        $this->taskSearchService = $taskSearchService;
        $this->authenticationService = $authenticationService;
    }

    #[SetAuditEventDescription('Case opgezocht')]
    public function search(SearchRequest $request): EncodableResponse
    {
        /** @var AuditEvent $event */
        $event = $this->auditService->getCurrentEvent();

        if ($event !== null) {
            $event->object(AuditObject::create('search', implode(', ', $request->all())));
        }

        $cases = $this->convertCases($this->collectCases($request));
        $tasks = $this->convertTasks($this->collectTasks($request));

        if ($event !== null && $cases->isNotEmpty()) {
            $event->objects(
                AuditObject::createArray(
                    $cases->toArray(),
                    static fn(array $case) => AuditObject::create('case', $case['uuid'])
                ),
            );
        }

        if ($event !== null && $tasks->isNotEmpty()) {
            $event->objects(
                AuditObject::createArray(
                    $tasks->toArray(),
                    static fn(array $task) => AuditObject::create('task', $task['uuid'])
                ),
            );
        }

        return new EncodableResponse(
            [
                'query' => $request->validated(),
                'contacts' => $tasks->toArray(),
                'cases' => $cases->toArray(),
            ],
            Response::HTTP_OK,
        );
    }

    private function collectCases(SearchRequest $request): Collection
    {
        $searchIdentifier = $request->getIdentifier();

        if ($searchIdentifier !== null) {
            /** @var string $organisationUuid */
            $organisationUuid = $this->authenticationService->getRequiredSelectedOrganisation()->uuid;

            return $this->caseService->findCasesByIdentifierForOwningOrganisation(new CaseIdentifier($searchIdentifier), $organisationUuid);
        }
        return $this->caseSearchService->searchByRequest($request);
    }

    /**
     * @param Collection<int, EloquentCase> $cases
     */
    private function convertCases(Collection $cases): Collection
    {
        return $cases->map(fn(EloquentCase $case) => [
            'uuid' => $case->uuid,
            'number' => $this->caseNumber(is_string($case->caseId) ? $case->caseId : null),
            'hpzone_number' => $case->hpzoneNumber,
            'monster_number' => $case->testMonsterNumber,
            'dateOfSymptomOnset' => $case->date_of_symptom_onset instanceof DateTimeInterface ? $case->date_of_symptom_onset->format(
                'Y-m-d',
            ) : null,
        ]);
    }

    private function collectTasks(SearchRequest $request): Collection
    {
        $organisation = $this->authenticationService->getSelectedOrganisation();
        if ($organisation === null) {
            abort(403);
        }

        return $this->taskSearchService->searchByRequest($request, $organisation);
    }

    /**
     * @param Collection<int, EloquentTask> $contacts
     */
    private function convertTasks(Collection $contacts): Collection
    {
        return $contacts->map(function (EloquentTask $task) {
            $caseUuid = $task->case_uuid;
            $case = $this->caseService->getCaseByUuid($caseUuid);

            $taskUuid = $task->uuid;
            $fragments = $this->taskFragmentService->loadFragments($taskUuid, ['general', 'personalDetails'], true);

            /** @var General $general */
            $general = $fragments['general'];

            return [
                'uuid' => $task->uuid,
                'contactDate' => $general->dateOfLastExposure ? $general->dateOfLastExposure->format('Y-m-d') : null,
                'category' => $general->category->value ?? null,
                'index' => [
                    'number' => $this->caseNumber($case->caseId ?? null),
                    'relationship' => $general->relationship->value ?? $general->otherRelationship,
                ],
            ];
        });
    }

    private function caseNumber(?string $caseId = null, ?string $organisationPrefix = null): ?string
    {
        if ($caseId === null) {
            return null;
        }

        if ($caseId && !$organisationPrefix) {
            return $caseId;
        }

        return sprintf('%s-%s', $organisationPrefix, $caseId);
    }
}
