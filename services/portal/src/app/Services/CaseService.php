<?php

declare(strict_types=1);

namespace App\Services;

use App\Dto\ArchiveCasesResultDto;
use App\Events\Case\CaseOrganisationUpdated;
use App\Events\Case\CaseUpdatedByPlanner;
use App\Exceptions\UpdateOrganisationUnauthorizedException;
use App\Helpers\CaseIndexAgeCalculatorKeyHelper;
use App\Helpers\FeatureFlagHelper;
use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase;
use App\Models\CovidCase\PlannerCase;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\PlannerCase\ListOptions;
use App\Models\PlannerCase\PlannerView;
use App\Models\PlannerCase\PlannerViewCounts;
use App\Models\StatusIndexContactTracing;
use App\Models\ValueObjects\CaseIdentifier;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnServiceException;
use App\Repositories\CaseData;
use App\Repositories\CaseListRepository;
use App\Repositories\CasePlanningData;
use App\Repositories\CaseRepository;
use App\Repositories\Intake\IntakeRepository;
use App\Repositories\StateRepository;
use App\Repositories\TaskRepository;
use App\Services\BcoNumber\BcoNumberException;
use App\Services\Bsn\BsnService;
use App\Services\Intake\IntakeService;
use App\Services\Note\CaseNoteService;
use App\Services\Note\CaseNoteTypeFactory;
use App\Services\Osiris\NotificationService as OsirisNotificationService;
use Carbon\CarbonImmutable;
use DBCO\Shared\Application\Metrics\Events\AbstractEvent;
use DBCO\Shared\Application\Metrics\Events\CaseApprovedEvent;
use DBCO\Shared\Application\Metrics\Events\CompletedEvent;
use DBCO\Shared\Application\Metrics\Events\CreatedEvent;
use DBCO\Shared\Application\Metrics\Events\OpenedEvent;
use DBCO\Shared\Application\Metrics\Events\ReversedPairingEvent;
use Exception;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;
use Illuminate\Validation\UnauthorizedException;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use MinVWS\DBCO\Enum\Models\CasequalityFeedback;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use MinVWS\DBCO\Metrics\Services\EventService;
use Psr\Log\LoggerInterface;
use Webmozart\Assert\Assert;

use function assert;
use function config;
use function implode;
use function sprintf;

class CaseService
{
    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly IntakeRepository $intakeRepository,
        private readonly TaskRepository $taskRepository,
        private readonly AuthenticationService $authService,
        private readonly StateRepository $stateRepository,
        private readonly EventService $eventService,
        private readonly CaseFragmentService $caseFragmentService,
        private readonly BsnService $bsnService,
        private readonly CaseNoteService $caseNoteService,
        private readonly LoggerInterface $logger,
        private readonly IntakeService $intakeService,
        private readonly CaseListRepository $caseListRepository,
        private readonly CaseAssignmentService $assignmentService,
        private readonly CaseArchiveService $caseArchiveService,
        private readonly ConnectionInterface $db,
        private readonly PolicyVersionService $policyVersionService,
    ) {
    }

    public function getCase(string $caseUuid): CovidCase
    {
        return $this->getCovidCaseFromEloquentModel(
            $this->caseRepository->getCase($caseUuid),
        );
    }

    public function getCovidCaseFromEloquentModel(EloquentCase $case): CovidCase
    {
        return $this->caseRepository->caseFromEloquentModel($case);
    }

    public function getCaseIncludingSoftDeletes(string $caseUuid): ?EloquentCase
    {
        return $this->caseRepository->getCaseIncludingSoftDeletes($caseUuid);
    }

    public function getContactTasks(string $caseUuid): Collection
    {
        return $this->taskRepository->getTasks($caseUuid, TaskGroup::contact());
    }

    /**
     * Get a case by its uuid
     */
    public function getCaseByUuid(string $caseUuid): ?EloquentCase
    {
        return $this->caseRepository->getCaseByUuid($caseUuid);
    }

    /**
     * Get a case by its HPZone id, monsterNumber or bco number
     */
    public function getCaseByIdentifierForOrganisation(CaseIdentifier $identifier, string $organisationUuid): ?EloquentCase
    {
        return $this->caseRepository->getCaseByIdentifierForOrganisation($identifier, $organisationUuid);
    }

    /**
     * Find all cases with supplied identifier, including trashed
     *
     * @param CaseIdentifier $identifier hpzone number, case id or monster number
     */
    public function findCasesByIdentifierForOwningOrganisation(CaseIdentifier $identifier, string $organisationUuid): Collection
    {
        return $this->caseRepository->findCasesByIdentifierForOwningOrganisation($identifier, $organisationUuid);
    }

    /**
     * Restore a softdeleted case
     */
    public function restoreCase(EloquentCase $case): void
    {
        $this->caseRepository->restoreCase($case);
    }

    public function myCases(?array $status = null): LengthAwarePaginator
    {
        try {
            $user = $this->authService->getAuthenticatedUser();
        } catch (AuthenticationException $exception) {
            throw new UnauthorizedException($exception->getMessage(), $exception->getCode(), $exception);
        }

        return $this->caseRepository->getCasesByAssignedUser($user->uuid, $status);
    }

    /**
     * @throws BcoNumberException
     */
    public function createCase(CasePlanningData $case, bool $assignToCurrentUser = true): EloquentCase
    {
        $owner = null;

        try {
            $user = $this->authService->getAuthenticatedUser();
            $owner = $user->uuid;
        } catch (AuthenticationException $authenticationException) {
            $user = null;
        }

        $organisation = $this->authService->getSelectedOrganisation();

        $bcoPhase = BCOPhase::defaultItem();
        assert($bcoPhase instanceof BCOPhase);
        if ($organisation) {
            assert($organisation->bco_phase instanceof BCOPhase);
            $bcoPhase = $organisation->bco_phase;
        }
        $organisationUuid = $this->authService->getRequiredSelectedOrganisation()->uuid;
        $caseData = new CaseData(
            $owner,
            $bcoPhase,
            $organisationUuid,
            $case->assignedCaseListUuid,
            $case->organisationLabel,
            $case->pseudoBsnGuid,
            $case->priority,
            $case->caseLabels,
            $case->testMonsterNumber,
            $case->caseId,
            ContactTracingStatus::defaultItem(),
            $case->automaticAddressVerificationStatus,
        );

        if ($assignToCurrentUser && $user !== null && !$user->can(sprintf('list,%s', EloquentCase::class))) {
            $caseData->assignedUserUuid = $caseData->owner;
        }

        $eloquentCase = $this->caseRepository->createCase($caseData);
        $this->eventService->registerEvent(new CreatedEvent(CreatedEvent::ACTOR_STAFF, $eloquentCase->uuid));

        if ($eloquentCase->assigned_user_uuid !== null) {
            $this->assignmentService->registerCaseAssignment($eloquentCase);
        }
        return $eloquentCase;
    }

    /**
     * Creates a planner case. This method is run in a transaction as it does an insert and an update
     * of the new covidcase.
     *
     * @throws BcoNumberException
     */
    public function createPlannerCase(CovidCase\PlannerCase $plannerCase, bool $assignToCurrentUser = true): CovidCase
    {
        $bsn = null;
        $logBsnServiceException = false;

        if ($plannerCase->pseudoBsnGuid !== null && $this->authService->getSelectedOrganisation() !== null) {
            try {
                $bsn = $this->bsnService->getByPseudoBsnGuid(
                    $plannerCase->pseudoBsnGuid,
                    $this->authService->getSelectedOrganisation()->external_id,
                );
            } catch (BsnServiceException) {
                //Continue creating the new case. Log the error later and include uuid
                $logBsnServiceException = true;
            }
        }

        /** @var CovidCase */
        return $this->db->transaction(function () use ($plannerCase, $assignToCurrentUser, $bsn, $logBsnServiceException): CovidCase {
            $priority = $plannerCase->priority !== null ? Priority::from($plannerCase->priority) : Priority::none();

            $caseData = new CasePlanningData(
                $plannerCase->assignedCaseListUuid,
                $plannerCase->label,
                $plannerCase->pseudoBsnGuid,
                $priority,
                $plannerCase->caseLabels,
                $plannerCase->test !== null ? $plannerCase->test->monsterNumber : null,
                ContactTracingStatus::defaultItem(),
                isset($bsn)
                    ? AutomaticAddressVerificationStatus::verified()
                    : AutomaticAddressVerificationStatus::unverified(),
            );
            if (isset($plannerCase->general)) {
                $caseData->caseId = $plannerCase->general->reference;
            }

            $eloquentCase = $this->createCase($caseData, $assignToCurrentUser);

            if (isset($bsn) && $plannerCase->index) {
                $plannerCase->index->bsnCensored = $bsn->getCensoredBsn();
                $plannerCase->index->bsnLetters = $bsn->getLetters();
            }

            $fragments = [
                'index' => $plannerCase->index,
                'contact' => $plannerCase->contact,
                'test' => $plannerCase->test,
            ];

            if (isset($plannerCase->general)) {
                $fragments['general'] = $plannerCase->general;
            }

            $this->caseFragmentService->storeFragments($eloquentCase->uuid, $fragments);

            ExportCaseToOsiris::dispatchIfEnabled($eloquentCase->uuid, CaseExportType::INITIAL_ANSWERS);

            //Try to match intake to this new case
            if ($eloquentCase !== null) {
                $this->intakeService->matchCaseToIntake($eloquentCase);
            }

            if ($logBsnServiceException) {
                $this->logger->error(
                    'BSN service not available for retrieving bsn data by pseudoBsn. Continue storing new case. Uuid: ' . $eloquentCase->uuid,
                );
            }
            return $this->caseRepository->caseFromEloquentModel($eloquentCase);
        });
    }

    public function getPlannerCase(string $uuid): PlannerCase
    {
        $covidCase = $this->getCase($uuid);

        $plannerCase = new PlannerCase();
        $plannerCase->uuid = $covidCase->uuid;
        $plannerCase->pseudoBsnGuid = $covidCase->pseudoBsnGuid;
        $plannerCase->automaticAddressVerificationStatus = $covidCase->automaticAddressVerificationStatus;
        $plannerCase->priority = $covidCase->priority->value;

        if ($this->authService->getRequiredSelectedOrganisation()->uuid === $covidCase->organisationUuid) {
            $plannerCase->label = $covidCase->organisationLabel;
        } elseif ($this->authService->getRequiredSelectedOrganisation()->uuid === $covidCase->assignedOrganisationUuid) {
            $plannerCase->label = $covidCase->assignedOrganisationLabel;
        }
        $plannerCase->caseLabels = $covidCase->caseLabels;

        $fragments = $this->caseFragmentService->loadFragments($covidCase->uuid, ['general', 'index', 'contact', 'test']);
        foreach ($fragments as $name => $fragment) {
            $plannerCase->$name = $fragment;
        }

        return $plannerCase;
    }

    /**
     * @throws BsnException
     */
    public function updatePlannerCase(PlannerCase $case): bool
    {
        Assert::notNull($case->uuid);
        $covidCase = $this->getCase($case->uuid);

        if ($this->authService->getRequiredSelectedOrganisation()->uuid === $covidCase->organisationUuid) {
            $covidCase->organisationLabel = $case->label;
        } elseif ($this->authService->getRequiredSelectedOrganisation()->uuid === $covidCase->assignedOrganisationUuid) {
            $covidCase->assignedOrganisationLabel = $case->label;
        }

        if (
            $case->pseudoBsnGuid !== null
            && $covidCase->pseudoBsnGuid !== $case->pseudoBsnGuid
            && $covidCase->organisation !== null
        ) {
            $covidCase->pseudoBsnGuid = $case->pseudoBsnGuid;
            $organisationExternalId = $covidCase->organisation->externalId;

            $bsn = $this->bsnService->getByPseudoBsnGuid($covidCase->pseudoBsnGuid, $organisationExternalId);
            if ($case->index) {
                $case->index->bsnCensored = $bsn->getCensoredBsn();
                $case->index->bsnLetters = $bsn->getLetters();
            }
        }

        $priority = Priority::tryFromOptional($case->priority);
        if ($priority !== null) {
            $covidCase->priority = $priority;
        }

        $covidCase->caseLabels = $case->caseLabels;

        $this->caseRepository->updateCase($covidCase);

        $fragments = [
            'general' => $case->general,
            'index' => $case->index,
            'contact' => $case->contact,
            'test' => $case->test,
        ];

        $this->caseFragmentService->storeFragments($covidCase->uuid, $fragments);

        $eloquentCase = $this->caseRepository->getCase($covidCase->uuid);

        CaseUpdatedByPlanner::dispatch($eloquentCase);

        return true;
    }

    /*
     * @param CovidCase|EloquentCase|object $case Case entity
     */
    public function updateCase(object $case): bool
    {
        return $this->caseRepository->updateCase($case);
    }

    public function openCase(EloquentCase $case): void
    {
        $this->caseRepository->openCase($case);

        $this->eventService->registerEvent(new OpenedEvent(OpenedEvent::ACTOR_STAFF, $case->uuid));
    }

    public function markAsCopied(EloquentCase $case, ?EloquentTask $task, string $fieldName): void
    {
        $firstTime = $this->stateRepository->markFieldAsCopied($case->uuid, $task?->uuid, $fieldName);
        if (!$firstTime) {
            return;
        }

        if ($task !== null) {
            // Task level copy
            $task->copied_at = CarbonImmutable::now();
            $this->taskRepository->save($task);
        } else {
            $case->copied_at = CarbonImmutable::now();
            $this->caseRepository->save($case);
        }
    }

    public function updateContactStatus(
        EloquentCase $case,
        ?ContactTracingStatus $statusIndexContactTracing,
        ?string $forceOsirisNotification,
        ?CasequalityFeedback $casequalityFeedback,
        ?string $statusExplanation,
    ): void {
        $getAssignmentState = static fn(EloquentCase $case) => implode(
            '|',
            [$case->assigned_user_uuid, $case->assigned_case_list_uuid, $case->assigned_organisation_uuid],
        );

        $assignmentState = $getAssignmentState($case);

        $this->updateIndexContactStatus($case, $statusIndexContactTracing, $statusExplanation, $casequalityFeedback);

        if ($statusExplanation !== null) {
            $case->statusExplanation = $statusExplanation;
        }

        if ($assignmentState !== $getAssignmentState($case)) {
            $this->assignmentService->registerCaseAssignment($case);
        }

        if (!OsirisNotificationService::isOsirisNotificationRequiredForCase($case)) {
            return;
        }

        ExportCaseToOsiris::dispatchIfEnabled(
            $case->uuid,
            $forceOsirisNotification === null || $forceOsirisNotification === 'finished'
                ? CaseExportType::DEFINITIVE_ANSWERS
                : CaseExportType::INITIAL_ANSWERS,
        );
    }

    public function isPairingAllowed(EloquentCase $case): bool
    {
        if ($case->createdAt === null) {
            return false;
        }

        $caseCreatedAt = $case->createdAt->copy();
        $pairingAllowedInterval = config('misc.case.pairingAllowedInterval');

        return CarbonImmutable::now()->lessThan($caseCreatedAt->addSeconds($pairingAllowedInterval));
    }

    public function getPlannerViewCounts(?CaseList $caseList = null): PlannerViewCounts
    {
        if (FeatureFlagHelper::isDisabled('planner_case_count_enabled')) {
            return new PlannerViewCounts();
        }

        $organisation = $this->authService->getRequiredSelectedOrganisation();
        $caseListUuid = $caseList->uuid ?? null;

        $result = new PlannerViewCounts();
        $result->intakeList = $this->intakeRepository->getIntakesCount();
        $result->unassigned = $this->caseRepository->getPlannerViewCount($organisation, PlannerView::unassigned(), $caseListUuid);
        $result->assigned = $this->caseRepository->getPlannerViewCount($organisation, PlannerView::assigned(), $caseListUuid);
        if ($caseList === null) {
            $result->outsourced = $this->caseRepository->getPlannerViewCount($organisation, PlannerView::outsourced(), $caseListUuid);
            $result->queued = $this->caseRepository->getPlannerViewCount($organisation, PlannerView::queued(), $caseListUuid);
        }
        $result->archived = $this->caseRepository->getPlannerViewCount($organisation, PlannerView::archived(), $caseListUuid);
        $result->completed = $this->caseRepository->getPlannerViewCount($organisation, PlannerView::completed(), $caseListUuid);
        return $result;
    }

    public function getPlannerViewCases(ListOptions $options): Paginator
    {
        $organisation = $this->authService->getRequiredSelectedOrganisation();
        return $this->caseRepository->getPlannerViewCases($organisation, $options);
    }

    public function deleteCase(EloquentCase $case): void
    {
        $this->caseRepository->deleteCase($case);
    }

    /**
     * @throws BsnException
     * @throws Exception
     */
    public function updatePseudoBsn(EloquentCase $case, string $pseudoBsnGuid): void
    {
        $pseudoBsn = $this->bsnService->getByPseudoBsnGuid($pseudoBsnGuid, $case->organisation->external_id);

        $case->pseudoBsnGuid = $pseudoBsn->getGuid();
        $case->automatic_address_verification_status = AutomaticAddressVerificationStatus::verified();
        $this->caseRepository->save($case);

        /** @var CovidCase\Index $indexFragment */
        $indexFragment = $this->caseFragmentService->loadFragment($case->uuid, 'index');
        $indexFragment->bsnCensored = $pseudoBsn->getCensoredBsn();
        $indexFragment->bsnLetters = $pseudoBsn->getLetters();
        $this->caseFragmentService->storeFragment($case->uuid, 'index', $indexFragment);
    }

    /**
     * Update the BCO phase of the current case.
     */
    public function updateCaseBcoPhase(EloquentCase $case, BCOPhase $bcoPhase): void
    {
        $this->caseRepository->setBcoPhaseForCase($case, $bcoPhase);
    }

    /**
     * Update the BCO phase of multiple case.
     *
     * @param array $cases
     */
    public function updateCaseBcoPhaseMultiple(array $cases, BCOPhase $bcoPhase): void
    {
        $cases = $this->caseRepository->getCasesByUuids($cases);

        $this->db->transaction(function () use ($cases, $bcoPhase): void {
            foreach ($cases as $case) {
                $this->updateCaseBcoPhase($case, $bcoPhase);
            }
        });
    }

    /**
     * There are three possible scenario's for calling this function:
     * As a BCO user: $completeStatusChecked is always null
     * As a Dossierchecker: $completeStatusChecked = true : Approve and archive
     * As a Dossierchecker: $completeStatusChecked = false : Disapprove & don't change bco status
     */
    public function updateIndexContactStatus(
        EloquentCase $case,
        ?ContactTracingStatus $statusIndexContactTracing,
        ?string $statusExplanation,
        ?CasequalityFeedback $casequalityFeedback,
    ): void {
        $case->status_index_contact_tracing = $statusIndexContactTracing;

        if ($casequalityFeedback instanceof CasequalityFeedback) {
            $this->updateCaseStatusByCasequalityUser($case, $casequalityFeedback, $statusExplanation);
        } else {
            $this->updateCaseStatusByNonCasequalityUser($case, $statusIndexContactTracing, $statusExplanation);
        }
    }

    private function updateCaseStatusByCasequalityUser(
        EloquentCase $case,
        ?CasequalityFeedback $casequalityFeedback,
        ?string $statusExplanation,
    ): void {
        $isApproved = null;

        switch ($casequalityFeedback) {
            case CasequalityFeedback::approveAndArchive():
                $isApproved = true;
                break;
            case CasequalityFeedback::rejectAndReopen():
                $isApproved = false;
                break;
        }

        if ($casequalityFeedback === CasequalityFeedback::archive() || $casequalityFeedback === CasequalityFeedback::approveAndArchive()) {
            $this->caseArchiveService->archiveCase($case);
            ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DEFINITIVE_ANSWERS);
            $this->caseRepository->unassignUserOnCase($case);

            /** @var CaseList $caseList */
            $caseList = $case->assigned_case_list_uuid
                ? $this->caseListRepository->getCaseListByUuid($case->assigned_case_list_uuid, false)
                : null;

            // Unassigned caselist if caselist is queue
            if ($caseList !== null && $caseList->is_queue) {
                $this->caseRepository->unassignCaseListOnCase($case);
            }
        } elseif ($casequalityFeedback === CasequalityFeedback::complete()) {
            $this->markComplete($case);
            $this->caseRepository->unassignUserOnCase($case);
        } elseif ($casequalityFeedback === CasequalityFeedback::rejectAndReopen()) {
            $this->reopenCaseWithNote($case);
            $this->caseRepository->giveCaseBackToWorkDistributor($case);
        }

        if (!empty($statusExplanation) && $casequalityFeedback !== null) {
            $this->caseNoteService->createNote(
                $case->uuid,
                CaseNoteTypeFactory::fromCasequalityFeedback($casequalityFeedback),
                $statusExplanation,
                $this->authService->getAuthenticatedUser(),
            );
        }

        $this->setApproval($case, $isApproved);
    }

    private function updateCaseStatusByNonCasequalityUser(
        EloquentCase $case,
        ?ContactTracingStatus $contactTracingStatus,
        ?string $statusExplanation,
    ): void {
        if ($contactTracingStatus !== null) {
            $statusIndexContactTracing = StatusIndexContactTracing::fromString($contactTracingStatus->value);

            switch (true) {
                case $statusIndexContactTracing->isClosed() || $statusIndexContactTracing->isCompleted():
                    $this->markComplete($case);

                    break;
                case $contactTracingStatus === ContactTracingStatus::notStarted():
                    $this->reopenCase($case);

                    break;
                case $statusIndexContactTracing->isOpen():
                    $this->reopenCaseWithNote($case);

                    break;
            }
        }

        $this->setApproval($case, null);

        $this->caseRepository->giveCaseBackToWorkDistributor($case);

        if (empty($statusExplanation)) {
            return;
        }

        $this->caseNoteService->createNote(
            $case->uuid,
            CaseNoteType::caseReturned(),
            $statusExplanation,
            $this->authService->getAuthenticatedUser(),
        );
    }

    private function setApproval(EloquentCase $case, ?bool $isApproved): void
    {
        if ($isApproved === true && $case->isApproved !== $isApproved) {
            $this->eventService->registerEvent(new CaseApprovedEvent(AbstractEvent::ACTOR_STAFF, $case->uuid));
        }
        $this->caseRepository->setCaseApproval($case, $isApproved);
    }

    private function markComplete(EloquentCase $case): void
    {
        $this->caseRepository->markCaseComplete($case, $this->policyVersionService->getActivePolicyVersion()->uuid);
        $this->eventService->registerEvent(new CompletedEvent(CompletedEvent::ACTOR_STAFF, $case->uuid));
    }

    /**
     * Reverse pairing was excepted, update the case indexStatus.
     */
    public function markPairingRequestAccepted(EloquentCase $case): void
    {
        $this->caseRepository->markPairingRequestAccepted($case);
        //Reverse pairing successful, register event metric
        $this->eventService->registerEvent(new ReversedPairingEvent(ReversedPairingEvent::ACTOR_STAFF, $case->uuid));
    }

    /**
     * @param EloquentCase|array<string> $case A single EloquentCase or a list of case uuids
     *
     * @throws AuthenticationException
     */
    public function archiveDirectly(EloquentCase|array $case, string $note, bool $sendNotification): ArchiveCasesResultDto
    {
        $cases = $case instanceof EloquentCase
            ? Collection::make([$case])
            : Collection::make($this->caseRepository->getCasesByUuids($case));

        [
            $closeableCases,
            $uncloseableCases,
        ] = $cases->partition(static fn (EloquentCase $case): bool => $case->isClosable());

        $this->db->transaction(function () use ($closeableCases, $note, $sendNotification): void {
            $closeableCases->each(function (EloquentCase $case) use ($note, $sendNotification): void {
                $this->doArchive($case);
                $this->doCreateNote($case->uuid, $note);

                if ($sendNotification) {
                    ExportCaseToOsiris::dispatchIfEnabled($case->uuid, CaseExportType::DEFINITIVE_ANSWERS);
                }
            });
        });

        return new ArchiveCasesResultDto(
            $closeableCases->pluck('uuid'),
            $uncloseableCases->map(static fn (EloquentCase $case): array => ['uuid' => $case->uuid, 'caseId' => $case->caseId]),
        );
    }

    private function doArchive(EloquentCase $case): void
    {
        $this->caseArchiveService->archiveCase($case);
        $this->caseRepository->unassignUserOnCase($case);

        $caseList = $case->assignedCaseList;

        // Only when caseList is queue, then it should be unassigned
        if ($caseList !== null && $caseList->is_queue) {
            $this->caseRepository->unassignCaseListOnCase($case);
        }
    }

    private function doCreateNote(string $caseUuid, string $note): void
    {
        if ($note === '') {
            return;
        }

        $this->caseNoteService->createNote(
            $caseUuid,
            CaseNoteType::caseDirectlyArchived(),
            $note,
            $this->authService->getAuthenticatedUser(),
        );
    }

    public function reopenCase(EloquentCase $case): bool
    {
        return $this->caseRepository->reopenCase($case);
    }

    public function reopenCaseWithNote(EloquentCase $case, ?string $note = null): void
    {
        // Reopen the case
        $reopened = $this->reopenCase($case);

        $caseUuid = $case->uuid;

        // If case is reopened & note is present
        if (!$reopened) {
            return;
        }

        $this->caseNoteService->createNote(
            $caseUuid,
            CaseNoteType::caseReopened(),
            $note ?? '',
            $this->authService->getAuthenticatedUser(),
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function updateCaseOrganisation(EloquentCase $case, string $organisationUuid, ?string $note = ''): void
    {
        if (!$case->canChangeOrganisation()) {
            throw new UpdateOrganisationUnauthorizedException();
        }

        $caseUuid = $case->uuid;

        // Create note for case
        $noteType = CaseNoteType::caseChangedOrganisation();
        $user = $this->authService->getAuthenticatedUser();
        $this->caseNoteService->createNote($caseUuid, $noteType, $note ?? '', $user);

        // !!! IMPORTANT !!! NOTE SHOULD BE CREATED FIRST BECAUSE WE WON'T HAVE ACCESS AFTERWARDS !!!
        // update organisation on case
        $this->caseRepository->updateOrganisation($case, $organisationUuid);
        CaseOrganisationUpdated::dispatch($case);
    }

    public function calculateIndexAgeForCase(EloquentCase $case): void
    {
        $dateOfBirth = $case->index->dateOfBirth;

        if ($dateOfBirth === null) {
            $indexAge = null;
            $indexAgeCalculatorKey = null;
        } else {
            $indexAge = CarbonImmutable::now()->diffInYears($dateOfBirth);
            $indexAgeCalculatorKey = CaseIndexAgeCalculatorKeyHelper::getCalculatorKey($dateOfBirth);
        }

        $case->index_age = $indexAge;
        $case->index_age_calculator_key = $indexAgeCalculatorKey;
        $this->caseRepository->saveQuietlyWithoutTimestamps($case);
    }
}
