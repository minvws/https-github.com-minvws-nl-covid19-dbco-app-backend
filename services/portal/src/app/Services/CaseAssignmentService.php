<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\InvalidCaseAssignmentException;
use App\Helpers\Config;
use App\Models\Assignment\Assignment;
use App\Models\Assignment\AssignmentOption;
use App\Models\Assignment\Cache;
use App\Models\Assignment\CaseListAssignment;
use App\Models\Assignment\CaseListMenuOption;
use App\Models\Assignment\CaseListOption;
use App\Models\Assignment\DefaultCaseQueueOption;
use App\Models\Assignment\MenuOption;
use App\Models\Assignment\NullCaseListAssignment;
use App\Models\Assignment\NullCaseListOption;
use App\Models\Assignment\NullOrganisationAssignment;
use App\Models\Assignment\NullUserAssignment;
use App\Models\Assignment\Option;
use App\Models\Assignment\Options;
use App\Models\Assignment\OrganisationAssignment;
use App\Models\Assignment\OrganisationMenuOption;
use App\Models\Assignment\OrganisationOption;
use App\Models\Assignment\ReturnToOwnerOption;
use App\Models\Assignment\UnassignedOption;
use App\Models\Assignment\UserAssignment;
use App\Models\Assignment\UserOption;
use App\Models\CaseList\ListOptions;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase as CovidCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Repositories\CaseAssignmentHistoryRepository;
use App\Repositories\CaseListRepository;
use App\Repositories\CaseRepository;
use App\Repositories\OrganisationRepository;
use App\Repositories\UserRepository;
use App\Services\Timeline\TimelineService;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

use function array_chunk;
use function array_filter;
use function array_key_exists;
use function count;
use function is_null;
use function json_encode;

use const PHP_INT_MAX;

class CaseAssignmentService
{
    private const GET_CASES_BY_UUID_CHUNK_SIZE = 200;

    public function __construct(
        private readonly CaseRepository $caseRepository,
        private readonly CaseListRepository $caseListRepository,
        private readonly OrganisationRepository $organisationRepository,
        private readonly UserRepository $userRepository,
        private readonly AuthenticationService $authService,
        private readonly CaseAssignmentHistoryRepository $caseAssignmentHistoryRepository,
        private readonly TimelineService $timelineService,
    ) {
    }

    private function getCasesByUuids(array $caseUuids): Generator
    {
        // optimize retrieval vs memory usage
        $chunks = array_chunk($caseUuids, self::GET_CASES_BY_UUID_CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $cases = $this->caseRepository->getCasesByUuids(
                $chunk,
                ['organisation', 'assignedOrganisation', 'assignedCaseList', 'assignedUser'],
                ['uuid', 'bco_status', 'organisation_uuid', 'assigned_organisation_uuid', 'assigned_case_list_uuid', 'assigned_user_uuid', 'assigned_organisation_label', 'created_at', 'updated_at'],
                false,
            );

            foreach ($cases as $case) {
                yield $case;
            }
        }
    }

    private function getAssignableUsers(EloquentOrganisation $selectedOrganisation): Collection
    {
        $lastLoginThreshold = CarbonImmutable::now()
            ->subDays(Config::integer('misc.case.assignment.lastLoginThresholdNeededForCaseAssignmentInDays'))
            ->toImmutable();
        return $this->userRepository->getAssignableUsers($selectedOrganisation->uuid, $lastLoginThreshold);
    }

    private function assignmentForOrganisation(?string $organisationUuid): Assignment
    {
        if ($organisationUuid === null) {
            return new NullOrganisationAssignment();
        }

        $selectedOrganisation = $this->authService->getRequiredSelectedOrganisation();
        $organisation = $this->organisationRepository->getOutsourceOrganisation($selectedOrganisation->uuid, $organisationUuid);
        if (!isset($organisation)) {
            throw new InvalidCaseAssignmentException("Invalid assignment, organisation does not exist!");
        }

        return new OrganisationAssignment($organisation);
    }

    private function assignmentForCaseList(?string $caseListUuid): Assignment
    {
        if ($caseListUuid === null) {
            return new NullCaseListAssignment();
        }

        $caseList = $this->caseListRepository->getCaseListByUuid($caseListUuid, false);
        if (!isset($caseList)) {
            throw new InvalidCaseAssignmentException("Invalid assignment, case list does not exist!");
        }

        return new CaseListAssignment($caseList);
    }

    private function assignmentForUser(?string $userUuid): Assignment
    {
        if ($userUuid === null) {
            return new NullUserAssignment();
        }

        $user = $this->userRepository->getByUuid($userUuid);
        if (!isset($user)) {
            throw new InvalidCaseAssignmentException("Invalid assignment, user does not exist!");
        }

        return new UserAssignment($user);
    }

    /**
     * Assignment object for assignment array.
     *
     * Array can contain one of:
     * - assignedOrganisationUuid
     * - assignedCaseListUuid
     * - assignedUserUuid
     *
     * @param array $assignment
     */
    public function assignmentForArray(array $assignment): Assignment
    {
        if (count($assignment) !== 1) {
            throw new InvalidCaseAssignmentException("Invalid assignment, cannot determine assignment type");
        }

        if (array_key_exists('assignedOrganisationUuid', $assignment)) {
            return $this->assignmentForOrganisation($assignment['assignedOrganisationUuid']);
        }

        if (array_key_exists('assignedCaseListUuid', $assignment)) {
            return $this->assignmentForCaseList($assignment['assignedCaseListUuid']);
        }

        if (array_key_exists('assignedUserUuid', $assignment)) {
            return $this->assignmentForUser($assignment['assignedUserUuid']);
        }

        throw new InvalidCaseAssignmentException("Invalid assignment, cannot determine assignment type");
    }

    /**
     * Is valid user for assignment?
     *
     * @param CovidCase $case Case object or UUID.
     * @param Assignment $assignment One of assignedOrganisationUuid, assignedCaseListUuid, assignedUserUuid
     * @param bool $validateFull Don't make certain assumptions about the given assignment.
     */
    public function isValidAssignment(CovidCase $case, Assignment $assignment, bool $validateFull = true): bool
    {
        return $assignment->isValidForCaseWithSelectedOrganisation(
            $case,
            $this->authService->getRequiredSelectedOrganisation(),
            $validateFull,
        );
    }

    /**
     * @param array<string> $caseUuids
     */
    public function assignCases(array $caseUuids, Assignment $assignment): void
    {
        DB::transaction(function () use ($caseUuids, $assignment): void {
            foreach ($this->getCasesByUuids($caseUuids) as $case) {
                $this->assignCaseIfUnchanged($case, $assignment);
            }
        });
    }

    public function assignCase(CovidCase $case, Assignment $assignment): void
    {
        DB::transaction(fn () => $this->assignCaseIfUnchanged($case, $assignment));
    }

    /**
     * @throws AuthenticationException
     */
    private function assignCaseIfUnchanged(CovidCase $case, Assignment $assignment): bool
    {
        if (!$this->isValidAssignment($case, $assignment)) {
            throw new InvalidCaseAssignmentException('Invalid assignment ' . $case->uuid . ' / ' . json_encode($assignment));
        }

        $assignment->applyToCase($case);

        if (!$this->caseRepository->updateCaseIfUnchanged($case)) {
            return false;
        }

        $this->registerCaseAssignment($case);

        return true;
    }

    /**
     * Assign next case of the given queue to the logged in user.
     *
     * @param CaseList $caseList Case queue.
     *
     * @return string|null Assigned case UUID.
     *
     * @throws AuthenticationException
     */
    public function assignNextCase(CaseList $caseList): ?string
    {
        $user = $this->authService->getAuthenticatedUser();

        if (!$user->can('edit', CovidCase::class) || !$user->can('caseCanPickUpNew')) {
            throw new UnauthorizedException('User has no permission to edit cases');
        }

        if (!$caseList->is_queue) {
            return null;
        }

        $assignment = new CaseListAssignment($caseList);
        if (!$assignment->isValidForSelectedOrganisation($this->authService->getRequiredSelectedOrganisation())) {
            return null;
        }

        $uuid = $this->caseRepository->assignNextCase($caseList->uuid, $user->uuid);

        if ($uuid !== null) {
            $case = $this->caseRepository->getCaseByUuid($uuid);
            if ($case !== null) {
                $this->registerCaseAssignment($case);
            }
        }

        return $uuid;
    }

    /**
     * Get assignment options for the given cases.
     *
     * @param array $caseUuids
     */
    public function getAssignmentOptions(array $caseUuids): Options
    {
        $cache = new Cache();
        $options = $this->buildAssignmentOptions($cache);
        $this->updateAssignmentOptionsForCases($options, $caseUuids, $cache);
        return $options;
    }

    /**
     * Build the structure with *all* valid assignment options for the current organisation without regard to any case.
     *
     * When encoding, options that are not valid, will not be returned or be disabled.
     */
    private function buildAssignmentOptions(Cache $cache): Options
    {
        $selectedOrganisation = $this->authService->getRequiredSelectedOrganisation();

        $options = new Options();

        $options->addOption(new UnassignedOption());

        $caseListOptions = new ListOptions();
        $caseListOptions->perPage = PHP_INT_MAX;
        $allCaseLists = $this->caseListRepository->listCaseLists($caseListOptions)->items();
        $defaultCaseQueue = array_filter($allCaseLists, static fn ($cl) => $cl->isDefault && $cl->isQueue)[0] ?? null;
        $caseLists = array_filter($allCaseLists, static fn ($cl) => !$cl->isDefault || !$cl->isQueue);

        if (isset($defaultCaseQueue)) {
            $options->addOption(new DefaultCaseQueueOption(new CaseListAssignment($defaultCaseQueue)));
        }

        $caseListMenuOption = $options->addOption(new CaseListMenuOption());
        $caseListMenuOption->addChildOption(new NullCaseListOption());

        foreach ($caseLists as $caseList) {
            $assignment = new CaseListAssignment($caseList);
            if ($assignment->isValidForSelectedOrganisation($selectedOrganisation, false, $cache)) {
                $caseListMenuOption->addChildOption(new CaseListOption($assignment));
            }
        }

        $options->addOption(new ReturnToOwnerOption());

        $organisations = $this->organisationRepository->getOutsourceOrganisations($selectedOrganisation->uuid);
        $organisationMenuOption = $options->addOption(new OrganisationMenuOption());
        foreach ($organisations as $organisation) {
            $assignment = new OrganisationAssignment($organisation);
            if ($assignment->isValidForSelectedOrganisation($selectedOrganisation, true, $cache)) {
                $organisationMenuOption->addChildOption(new OrganisationOption($assignment));
            }
        }

        $users = $this->getAssignableUsers($selectedOrganisation);
        foreach ($users as $user) {
            $assignment = new UserAssignment($user);
            if ($assignment->isValidForSelectedOrganisation($selectedOrganisation, false, $cache)) {
                $options->addOption(new UserOption($assignment));
            }
        }

        return $options;
    }

    /**
     * Update assignment options with their selected and enabled counts.
     *
     * @param array $caseUuids
     */
    private function updateAssignmentOptionsForCases(Options $options, array $caseUuids, Cache $cache): void
    {
        $selectedOrganisation = $this->authService->getRequiredSelectedOrganisation();
        $selectableOptions = $options->getSelectableOptions();
        foreach ($this->getCasesByUuids($caseUuids) as $case) {
            foreach ($selectableOptions as $option) {
                $this->updateAssignmentOptionForCase($option, $case, $selectedOrganisation, $cache);
            }
        }
    }

    /**
     * Update assignment option with selected/enabled state for the given case.
     */
    private function updateAssignmentOptionForCase(Option $option, CovidCase $case, EloquentOrganisation $selectedOrganisation, Cache $cache): void
    {
        $assignedOrganisationUuid = $case->getRawOriginal('assigned_organisation_uuid') ?? $case->assigned_organisation_uuid;
        $isAssignedToDifferentOrganisation = !is_null($assignedOrganisationUuid)
            && $assignedOrganisationUuid !== ($selectedOrganisation->getRawOriginal('uuid') ?? $selectedOrganisation->uuid);

        if ($option instanceof UnassignedOption) {
            $selected = $case->assignedUser === null && ($case->assignedCaseList === null || !$case->assignedCaseList->is_default);
            $option->incrementSelected(!$isAssignedToDifferentOrganisation && $selected);
            $option->incrementEnabled(!$selected && !$isAssignedToDifferentOrganisation);
            return;
        }

        if ($option instanceof ReturnToOwnerOption) {
            $option->updateForCase($case, $selectedOrganisation, false, $cache);
            return;
        }

        if ($option instanceof MenuOption) {
            $option->incrementSelected(false);
            $option->incrementEnabled(!$isAssignedToDifferentOrganisation);
            return;
        }

        if ($option instanceof AssignmentOption) {
            $option->updateForCase($case, $selectedOrganisation, false, $cache);
            return;
        }

        if ($option instanceof NullCaseListOption) {
            $option->updateForCase($case, $selectedOrganisation, false, $cache);
        }
    }

    /**
     * Get all user assignment options.
     */
    public function getUserAssignmentOptions(): Options
    {
        $options = new Options();
        $users = $this->getAssignableUsers($this->authService->getRequiredSelectedOrganisation());

        foreach ($users as $user) {
            $option = new UserOption(new UserAssignment($user));
            $option->incrementEnabled(true);
            $options->addOption($option);
        }

        return $options;
    }

    /**
     * Store case assignment in the case_assignment_history.
     *
     * @throws AuthenticationException
     */
    public function registerCaseAssignment(CovidCase $case): void
    {
        $assigner = $this->authService->getAuthenticatedUser();
        $caseAssignmentHistory = $this->caseAssignmentHistoryRepository->registerCaseAssignment($case, $assigner);
        $this->timelineService->addToTimeline($caseAssignmentHistory);
    }
}
