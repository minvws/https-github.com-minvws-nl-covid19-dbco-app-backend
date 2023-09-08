<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\CovidCase;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\PlannerCase\ListOptions;
use App\Models\PlannerCase\PlannerView;
use App\Models\ValueObjects\CaseIdentifier;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;

interface CaseRepository
{
    /**
     * Returns the case and its task list.
     *
     * @throws ModelNotFoundException
     */
    public function getCase(string $caseUuid): EloquentCase;

    /**
     * Optimized retrieval of case with its relations for export purposes.
     */
    public function getCaseForExport(string $caseUuid): ?EloquentCase;

    /**
     * Returns the case, whether it's soft deleted or not
     */
    public function getCaseIncludingSoftDeletes(string $caseUuid): ?EloquentCase;

    /**
     * Restore a case
     */
    public function restoreCase(EloquentCase $case): void;

    /**
     * Get a case by uuid
     */
    public function getCaseByUuid(string $caseUuid): ?EloquentCase;

    /**
     * Get a case by HPZone id, monsterNumber or bco number
     */
    public function getCaseByIdentifierForOrganisation(CaseIdentifier $identifier, string $organisationUuid): ?EloquentCase;

    /**
     * Find all cases with supplied identifier, including trashed
     *
     * @param CaseIdentifier $identifier hpzone number, case id or monster number
     *
     * @return Collection<int, EloquentCase>
     */
    public function findCasesByIdentifierForOwningOrganisation(CaseIdentifier $identifier, string $organisationUuid): Collection;

    /**
     * Fetch multiple cases by UUIDs.
     *
     * @param array $caseUuids
     * @param array $with
     * @param array $columns
     *
     * @return array<EloquentCase>
     */
    public function getCasesByUuids(array $caseUuids, array $with = [], array $columns = [], bool $withCaseAuthScope = true): array;

    /**
     * Fetch multiple searched cases by UUIDs.
     *
     * @return array<EloquentCase>
     */
    public function getSearchedCasesByUuids(array $caseUuids, string $organisationUuid, array $with = [], array $columns = []): array;

    /**
     * Returns all the cases of a user
     */
    public function getCasesByAssignedUser(string $userUuid, ?array $bcoStatus = null): LengthAwarePaginator;

    /**
     * Create a new, empty case.
     */
    public function createCase(CaseData $case): EloquentCase;

    /**
     * Update case.
     *
     * @param CovidCase|EloquentCase|object $case Case entity
     *
     * @deprecated Use save()
     */
    public function updateCase(object $case): bool;

    /**
     * Delete a case by it's UUID.
     */
    public function deleteCase(EloquentCase $case): void;

    /**
     * Update case if not changed outside this process.
     *
     * @param EloquentCase $case Case entity
     *
     * @return bool Updated?
     */
    public function updateCaseIfUnchanged(EloquentCase $case): bool;

    public function setExpiry(string $caseUuid, DateTimeImmutable $windowExpiresAt, DateTimeImmutable $pairingExpiresAt): mixed;

    /**
     * Returns the total number of cases for the given planner view.
     */
    public function getPlannerViewCount(EloquentOrganisation $organisation, PlannerView $view, ?string $caseListUuid): ?int;

    /**
     * Returns paginated cases for the given planner view.
     */
    public function getPlannerViewCases(EloquentOrganisation $organisation, ListOptions $options): Paginator;

    /**
     * Assigns the next available case for a given queue to the given user.
     *
     * @return string|null Case UUID.
     */
    public function assignNextCase(string $caseListUuid, string $userUuid): ?string;

    /**
     * Covert the EloquentCase model to a CovidCase model.
     *
     * @deprecated Use EloquentCase
     */
    public function caseFromEloquentModel(EloquentCase $dbCase): CovidCase;

    /**
     * @param array $conditions
     *
     * @return Collection<int, EloquentCase>
     */
    public function searchCases(array $conditions): Collection;

    /**
     * @return Collection<int, EloquentCase>
     */
    public function getCasesByPseudoBsnGuid(string $pseudoBsnGuid, array $ignoreUuids = []): Collection;

    /**
     * @return Collection<int, EloquentCase>
     */
    public function getArchivedWithLabelsOrPriority(): Collection;

    /**
     * @return Collection<int, EloquentCase>
     */
    public function getStaleCasesByBCOStatus(int $stalePeriodInDays, BCOStatus $bcoStatus): Collection;

    public function save(EloquentCase $eloquentCase): void;

    public function saveQuietlyWithoutTimestamps(EloquentCase $eloquentCase): void;

    public function addCaseLabel(EloquentCase $case, CaseLabel $caseLabel): void;

    public function addCaseLabels(EloquentCase $case, Collection $caseLabels): void;

    public function openCase(EloquentCase $case): bool;

    public function reopenCase(EloquentCase $case): bool;

    public function giveCaseBackToWorkDistributor(EloquentCase $case): bool;

    public function markCaseComplete(EloquentCase $case, ?string $policyVersionUuid = null): bool;

    public function updateOrganisation(EloquentCase $case, string $organisationUuid): bool;

    public function findCaseByPseudoBsnGuidCreatedAfter(string $pseudoBsnGuid, DateTimeInterface $createdAfterDate): ?EloquentCase;

    public function unassignUserOnCase(EloquentCase $case): bool;

    public function archive(EloquentCase $case, ?string $policyVersionUuid = null): bool;

    public function unassignCaseListOnCase(EloquentCase $case): bool;

    public function setCaseApproval(EloquentCase $case, ?bool $isApproved): bool;

    /**
     * @param callable(Collection<int,EloquentCase>):void $callback
     */
    public function chunkCasesBetweenDates(
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $field,
        int $chunkSize,
        callable $callback,
    ): void;

    public function setBcoPhaseForCase(EloquentCase $case, BCOPhase $bcoPhase): void;

    public function markPairingRequestAccepted(EloquentCase $case): void;
}
