<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AuditObjectHelper;
use App\Http\Controllers\Api\Traits\ValidatesModels;
use App\Http\Requests\Api\BcoPhaseMultiUpdateRequest;
use App\Http\Requests\Api\BcoPhaseUpdateRequest;
use App\Http\Requests\Api\Case\MarkAsCopiedRequest;
use App\Http\Requests\Api\CovidCase\ArchiveCaseDirectlyMultipleRequest;
use App\Http\Requests\Api\CovidCase\ArchiveCaseDirectlySingleRequest;
use App\Http\Requests\Api\CovidCase\CheckUnansweredQuestionsRequest;
use App\Http\Requests\Api\CovidCase\UpdateCaseOrganisationRequest;
use App\Http\Requests\Api\CovidCase\UpdateContactStatusRequest;
use App\Http\Requests\Api\Note\CreateRequest as NoteCreateRequest;
use App\Http\Requests\PseudoBsnUpdateRequest;
use App\Http\Responses\Api\EloquentCase\EloquentCaseEncoder;
use App\Http\Responses\EncodableResponse;
use App\Http\Responses\EncodableResponseBuilder;
use App\Models\Eloquent\EloquentCase;
use App\Policies\EloquentCasePolicy;
use App\Services\AuthenticationService;
use App\Services\CaseConnectedService;
use App\Services\CaseLockService;
use App\Services\CaseService;
use App\Services\Note\CaseNoteService;
use App\Services\Osiris\CaseUnansweredQuestionsService;
use App\Services\TaskService;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use MinVWS\Audit\Attribute\SetAuditEventDescription;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\Audit\Services\AuditService;
use MinVWS\Codable\EncodingContext;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Webmozart\Assert\Assert;

use function array_map;
use function sprintf;

class ApiCaseController extends ApiController
{
    use ValidatesModels;

    public function __construct(
        private readonly CaseConnectedService $caseConnectedService,
        private readonly CaseService $caseService,
        private readonly TaskService $taskService,
        private readonly AuthenticationService $authService,
        private readonly CaseUnansweredQuestionsService $caseUnansweredQuestionsService,
        private readonly EloquentCasePolicy $casePolicy,
        private readonly CaseLockService $caseLockService,
        private readonly AuditService $auditService,
        private readonly EloquentCaseEncoder $caseEncoder,
        private readonly CaseNoteService $caseNoteService,
        private readonly ResponseFactory $response,
        private readonly UrlGenerator $urlGenerator,
    ) {
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Case opgehaald')]
    public function getCase(EloquentCase $eloquentCase, AuditEvent $auditEvent): mixed
    {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        Assert::notNull($caseAuditObject);

        $auditEvent->object($caseAuditObject);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        $authenticatedUser = $this->authService->getAuthenticatedUser();

        // Add a case lock if we are going into edit mode
        if (
            $this->casePolicy->edit($authenticatedUser, $eloquentCase)
            && !$this->caseLockService->hasCaseLock($eloquentCase)
        ) {
            $this->caseLockService->addCaseLock($eloquentCase, $authenticatedUser);
        }

        return $this->auditService->registerHttpEvent(
            AuditEvent::create(
                __METHOD__,
                AuditEvent::ACTION_READ,
                AuditEventHelper::getAuditEventDescriptionByActionName(__METHOD__),
            ),
            fn () => EncodableResponseBuilder::create($eloquentCase)
                ->withContext(function (EncodingContext $context): void {
                    $context->registerDecorator(EloquentCase::class, $this->caseEncoder);
                })->build()
        );
    }

    #[SetAuditEventDescription('Cases opgehaald van ingelogde gebruiker')]
    public function myCases(AuditEvent $auditEvent, ?string $status = null): JsonResponse
    {
        if ($status !== null) {
            $status = BCOStatus::from($status);
        }

        if ($status === BCOStatus::completed()) {
            return $this->response->json([], HttpResponse::HTTP_NOT_FOUND);
        }

        $myCases = $this->caseService->myCases($status === null ? null : [$status]);

        $auditEvent->objects(
            AuditObject::createArray(
                $myCases->items(),
                static fn ($case) => AuditObject::create('case', $case->uuid)
            ),
        );

        $this->viewEnhancements($myCases);

        return $this->response->json(['cases' => $myCases], HttpResponse::HTTP_OK);
    }

    private function viewEnhancements(LengthAwarePaginator $cases): void
    {
        // Enrich my cases data with some view level helper data
        foreach ($cases->items() as $case) {
            $case->editCommand = $this->urlGenerator->route('case-edit', [$case->uuid]);
        }
    }

    /**
     * @throws AuthenticationException
     */
    public function markAsCopied(
        MarkAsCopiedRequest $request,
        AuditService $auditService,
        EloquentCasePolicy $casePolicy,
    ): JsonResponse {
        $auditService->setEventExpected(false);

        $caseId = $request->getCaseId();
        $taskUuid = $request->getTaskId();
        $fieldName = $request->getFieldName();

        if ($caseId === '') {
            return $this->response->json(
                ['error' => sprintf('Case %s is invalid', $caseId)],
                HttpResponse::HTTP_BAD_REQUEST,
            );
        }

        $case = $this->caseService->getCaseByUuid($caseId);
        $user = $this->authService->getAuthenticatedUser();

        if ($case === null || !$casePolicy->edit($user, $case)) {
            return $this->response->json(['error' => 'Access denied'], HttpResponse::HTTP_FORBIDDEN);
        }

        $task = null;
        if ($taskUuid !== null) {
            $task = $this->taskService->getTaskByUuid($taskUuid);

            if ($task === null) {
                return $this->response->json(
                    ['error' => sprintf('Task %s is invalid', $taskUuid)],
                    HttpResponse::HTTP_BAD_REQUEST,
                );
            }

            if (!$user->can('taskEdit', $this->taskService->getTaskByUuid($task->uuid))) {
                return $this->response->json(['error' => 'Access denied'], HttpResponse::HTTP_FORBIDDEN);
            }
        }

        $this->caseService->markAsCopied($case, $task, $fieldName);

        return $this->response->json(['success' => 'success'], HttpResponse::HTTP_OK);
    }

    public function getContactStatus(EloquentCase $eloquentCase, AuditEvent $auditEvent): JsonResponse
    {
        Assert::keyExists($auditEvent->getObjects(), 0);

        AuditObjectHelper::setAuditObjectOrganisation($auditEvent->getObjects()[0], $eloquentCase);

        return $this->response->json([
            'bcoStatus' => $eloquentCase->bcoStatus,
            'indexStatus' => $eloquentCase->indexStatus,
            'statusIndexContactTracing' => $eloquentCase->status_index_contact_tracing,
            'statusExplanation' => $eloquentCase->status_explanation,
        ]);
    }

    #[SetAuditEventDescription('Contact status bijgewerkt')]
    public function updateContactStatus(
        EloquentCase $case,
        UpdateContactStatusRequest $request,
        AuditEvent $auditEvent,
    ): EncodableResponse {
        Assert::keyExists($auditEvent->getObjects(), 0);

        AuditObjectHelper::setAuditObjectOrganisation($auditEvent->getObjects()[0], $case);

        $this->caseService->updateContactStatus(
            $case,
            $request->contactTracingStatus(),
            $request->forceOsirisNotification(),
            $request->casequalityFeedback(),
            $request->statusExplanation(),
        );

        return EncodableResponseBuilder::create($case)
            ->withContext(function (EncodingContext $context): void {
                $context->registerDecorator(EloquentCase::class, $this->caseEncoder);
            })->build();
    }

    /**
     * @throws Exception
     */
    public function checkUnansweredQuestions(
        EloquentCase $eloquentCase,
        CheckUnansweredQuestionsRequest $request,
    ): JsonResponse {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        Assert::notNull($caseAuditObject);

        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        $version = $request->getVersion();

        switch ($version) {
            case null:
            case 'finished':
                $finished = true;
                break;
            case 'pre-notification':
                $finished = false;
                break;
            default:
                $error = sprintf('Invalid version "%s" given. Available values are "finished", "pre-notification"', $version);
                return $this->response->json(['error' => $error], HttpResponse::HTTP_BAD_REQUEST);
        }

        $validationResult = $this->caseUnansweredQuestionsService->getByCaseUuid($eloquentCase->uuid, $finished);

        $responseData = [
            'is_missing' => !$validationResult->isValid(),
            'fields' => $validationResult->getErrors(),
        ];

        return $this->response->json($responseData);
    }

    /**
     * @throws Exception
     */
    #[SetAuditEventDescription('Pseudo BSN bijgewerkt')]
    public function updatePseudoBsn(
        EloquentCase $eloquentCase,
        PseudoBsnUpdateRequest $request,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        Assert::notNull($caseAuditObject);
        $auditEvent->object($caseAuditObject);

        $bsnAuditObject = AuditObject::create('pseudo-bsn');
        $bsnAuditObject->identifier($eloquentCase->pseudoBsnGuid ?? '');
        $bsnAuditObject->detail('newPseudoBsnGuid', $request->getPseudoBsnGuid());
        $auditEvent->object($bsnAuditObject);

        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        $this->caseService->updatePseudoBsn($eloquentCase, $request->getPseudoBsnGuid());

        return $this->response->json(['case' => $this->caseService->getCovidCaseFromEloquentModel($eloquentCase)]);
    }

    #[SetAuditEventDescription('Gekoppelde cases en contacten opgehaald')]
    public function connected(EloquentCase $eloquentCase, AuditEvent $auditEvent): JsonResponse
    {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        $auditEvent->object($caseAuditObject);

        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        if ($eloquentCase->pseudo_bsn_guid === null) {
            return $this->response->json(['error' => 'Case has no PseudoBSN'], HttpResponse::HTTP_BAD_REQUEST);
        }

        $connectedCasesAndTasks = $this->caseConnectedService->getConnectedCasesAndTasksForCase($eloquentCase);
        $caseAuditObject->detail(
            'connectedCases',
            array_map(static fn ($c) => $c['uuid'], $connectedCasesAndTasks['cases']->toArray()),
        );
        $caseAuditObject->detail(
            'connectedTasks',
            array_map(static fn ($c) => $c['uuid'], $connectedCasesAndTasks['tasks']->toArray()),
        );

        return $this->response->json($connectedCasesAndTasks);
    }

    #[SetAuditEventDescription('Huidige case BCO fase bijgewerkt')]
    public function updateCaseBcoPhase(EloquentCase $eloquentCase, BcoPhaseUpdateRequest $request): JsonResponse
    {
        $this->caseService->updateCaseBcoPhase($eloquentCase, $request->getBcoPhase());

        return $this->response->json(['message' => 'Case BCO phase updated successfully']);
    }

    #[SetAuditEventDescription('Huidige case BCO fase bijgewerkt')]
    public function updateCaseBcoPhaseMulti(BcoPhaseMultiUpdateRequest $request): JsonResponse
    {
        $this->caseService->updateCaseBcoPhaseMultiple($request->getCases(), $request->getBcoPhase());

        return $this->response->json(['message' => 'Cases BCO phase updated successfully']);
    }

    /**
     * @throws AuthenticationException
     */
    public function archiveCaseDirectly(ArchiveCaseDirectlySingleRequest $request, EloquentCase $case): JsonResponse
    {
        if (!$case->isClosable()) {
            return $this->response->json(['message' => 'Case could not be archived'], 400);
        }

        $this->caseService->archiveDirectly($case, $request->getNote(), $request->getSendOsirisNotification());

        return $this->response->json(['message' => 'Case archived successfully']);
    }

    /**
     * @throws AuthenticationException
     */
    public function archiveCaseDirectlyMulti(ArchiveCaseDirectlyMultipleRequest $request): JsonResponse
    {
        $archiveResult = $this->caseService->archiveDirectly(
            $request->getCases(),
            $request->getNote(),
            $request->getSendOsirisNotification(),
        );

        return $this->response->json(
            ['invalid_cases' => $archiveResult->invalidCases->pluck('uuid', 'caseId')],
            $archiveResult->closedCases->isNotEmpty() ? 200 : 400,
        );
    }

    #[SetAuditEventDescription('Afgesloten case opnieuw geopend')]
    public function reopenCase(Request $request, EloquentCase $eloquentCase, AuditEvent $auditEvent): JsonResponse
    {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        Assert::notNull($caseAuditObject);

        $auditEvent->object($caseAuditObject);
        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        // Check if case is able to reopen
        // -- We should never see this error if the front-end correctly shows the button on the correct time
        if (!$eloquentCase->isReopenable()) {
            return $this->response->json(['error' => 'Forbidden to reopen case'], 403);
        }

        // Reopen the given case
        $note = $request->string('note');
        $note = $note->isEmpty() ? null : $note->toString();
        $this->caseService->reopenCaseWithNote($eloquentCase, $note);

        return $this->response->json(['message' => 'Case has been reopened']);
    }

    /**
     * @throws AuthenticationException
     */
    public function updateCaseOrganisation(
        UpdateCaseOrganisationRequest $request,
        EloquentCase $eloquentCase,
        AuditEvent $auditEvent,
    ): JsonResponse {
        $caseAuditObject = AuditObject::create('case', $eloquentCase->uuid);
        $auditEvent->object($caseAuditObject);

        AuditObjectHelper::setAuditObjectOrganisation($caseAuditObject, $eloquentCase);

        $this->caseService->updateCaseOrganisation($eloquentCase, $request->getOrganisationUuid(), $request->getNote());

        return $this->response->json();
    }

    /**
     * @throws AuthenticationException
     */
    #[SetAuditEventDescription('Notitie gemaakt op case')]
    public function createNote(EloquentCase $case, NoteCreateRequest $request): JsonResponse
    {
        $this->caseNoteService->createNote(
            $case->uuid,
            CaseNoteType::from($request->getType()),
            $request->getNote(),
            $this->authService->getAuthenticatedUser(),
        );

        return $this->response->json('', Response::HTTP_CREATED);
    }
}
