<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\Range;
use App\Models\CovidCase;
use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Export\Cursor;
use App\Models\PlannerCase\ListOptions;
use App\Models\PlannerCase\PlannerSort;
use App\Models\PlannerCase\PlannerView;
use App\Models\ValueObjects\CaseIdentifier;
use App\Scopes\CaseAuthScope;
use App\Scopes\OrganisationAuthScope;
use App\Services\BcoNumber\BcoNumberException;
use App\Services\Chores\ChoreService;
use App\Services\Export\Helpers\ExportFetchMutationsHelper;
use Carbon\CarbonImmutable;
use DateTimeImmutable;
use DateTimeInterface;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Query\Expression;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorImpl;
use Illuminate\Pagination\Paginator as PaginatorImpl;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use Webmozart\Assert\Assert;

use function array_search;
use function collect;
use function count;
use function is_array;
use function is_int;
use function is_null;

class DbCaseRepository implements CaseRepository
{
    public function __construct(
        private readonly OrganisationRepository $organisationRepository,
        private readonly ChoreService $choreService,
        private readonly ExportFetchMutationsHelper $fetchMutationsHelper,
        private readonly Config $config,
    ) {
    }

    /**
     * @throws ModelNotFoundException
     */
    public function getCase(string $caseUuid): EloquentCase
    {
        return EloquentCase::where('uuid', $caseUuid)->firstOrFail();
    }

    /**
     * Optimized retrieval of case with its relations for export purposes.
     */
    public function getCaseForExport(string $caseUuid): ?EloquentCase
    {
        return
            EloquentCase::where('uuid', $caseUuid)
            ->with([
                'organisation',
                'createdBy',
                'assignedUser',
                'assignedCaseList',
                'assignedOrganisation',
                'tasks',
                'contexts',
                'contexts.sections',
                'contexts.moments',
            ])->first();
    }

    public function getCaseByUuid(string $caseUuid): ?EloquentCase
    {
        return EloquentCase::query()->find($caseUuid);
    }

    /**
     * @param CaseIdentifier $identifier HPZone Case identifier.
     */
    public function getCaseByIdentifierForOrganisation(CaseIdentifier $identifier, string $organisationUuid): ?EloquentCase
    {
        $searchColumn = $this->getSearchColumn($identifier);

        return EloquentCase::where($searchColumn, $identifier->getIdentifier())
            ->where(static function (Builder $query) use ($organisationUuid): void {
                $query->where('covidcase.organisation_uuid', $organisationUuid);
                $query->orWhere('covidcase.assigned_organisation_uuid', $organisationUuid);
            })
            ->first();
    }

    /**
     * @return Collection<int, EloquentCase>
     */
    public function findCasesByIdentifierForOwningOrganisation(CaseIdentifier $identifier, string $organisationUuid): Collection
    {
        $searchColumn = $this->getSearchColumn($identifier);

        return EloquentCase::where($searchColumn, $identifier->getIdentifier())
            ->where('covidcase.organisation_uuid', $organisationUuid)
            ->withTrashed()
            ->get();
    }

    public function getCaseIncludingSoftDeletes(string $caseUuid): ?EloquentCase
    {
        return EloquentCase::withTrashed()->where('uuid', $caseUuid)->first();
    }

    /**
     * @inheritDoc
     */
    public function getCasesByUuids(
        array $caseUuids,
        array $with = [],
        array $columns = ['*'],
        bool $withCaseAuthScope = true,
    ): array {
        $builder = EloquentCase::query();

        if (!$withCaseAuthScope) {
            $builder->withoutGlobalScope(CaseAuthScope::class);
        }

        $builder->whereIn('uuid', $caseUuids);
        $builder->with($with);
        $result = $builder->get($columns);

        return $result->all();
    }

    public function getSearchedCasesByUuids(
        array $caseUuids,
        string $organisationUuid,
        array $with = [],
        array $columns = ['*'],
    ): array {
        $builder = EloquentCase::query();
        $builder->withoutGlobalScope(CaseAuthScope::class);

        $builder->where('organisation_uuid', $organisationUuid);
        $builder->whereIn('uuid', $caseUuids);

        $builder->with($with);
        $result = $builder->get($columns);

        return $result->all();
    }

    public function restoreCase(EloquentCase $case): void
    {
        $case->restore();
    }

    public function getCasesByAssignedUser(string $userUuid, ?array $bcoStatus = null): LengthAwarePaginator
    {
        $builder = EloquentCase::where('assigned_user_uuid', $userUuid)
            ->select('covidcase.*', 'bcouser.name as assigned_name')
            ->join('bcouser', 'bcouser.uuid', '=', 'covidcase.assigned_user_uuid');
        $builder->with(['organisation', 'caseLabels', 'index']);

        if (is_array($bcoStatus)) {
            $builder->whereIn('covidcase.bco_status', $bcoStatus);
        }

        $builder->orderByDesc('covidcase.updated_at');

        $paginator = $builder->paginate($this->config->get('view.rowsPerPage'));

        $cases = [];

        foreach ($paginator->items() as $dbCase) {
            $case = $this->caseFromEloquentModel($dbCase);
            $cases[] = $case;
        }

        $paginator->setCollection(collect($cases));
        return $paginator;
    }

    private function addRecentlyUpdatedFilter(Builder $query): void
    {
        $days = (int) $this->config->get('misc.planner.case_recent_days');
        $query->whereRaw('updated_at >= NOW() - INTERVAL ' . $days . ' DAY');
    }

    private function addViewFilter(Builder $query, EloquentOrganisation $organisation, PlannerView $view, ?string $caseListUuid): void
    {
        if ($view === PlannerView::outsourced()) {
            $this->addOrganisationFilter($query, $organisation);
        } else {
            $this->addCurrentOrganisationFilter($query, $organisation);
        }

        $viewColumn = 'current_organisation_planner_view';
        if ($caseListUuid !== null) {
            $viewColumn = 'case_list_planner_view';
        } elseif ($view === PlannerView::outsourced()) {
            $viewColumn = 'organisation_planner_view';
        }

        $query->where($viewColumn, $view->value);

        if ($view === PlannerView::archived()) {
            $this->addRecentlyUpdatedFilter($query);
        }
    }

    private function addViewOrderBy(Builder $query, ListOptions $options, ?string $caseListUuid): void
    {
        if ($options->sort !== null) {
            $sortOrder = $options->order ?? 'asc';

            switch ($options->sort) {
                case PlannerSort::updatedAt():
                    $query->orderBy('covidcase.updated_at', $sortOrder);
                    break;
                case PlannerSort::createdAt():
                    $query->orderBy('covidcase.created_at', $sortOrder);
                    break;
                case PlannerSort::contactsCount():
                    $query->orderByRaw(
                        '(select count(*) from `task` where `covidcase`.`uuid` = `task`.`case_uuid` and `task`.`deleted_at` is null) ' . $sortOrder,
                    );
                    break;
                case PlannerSort::caseStatus():
                    $query->orderBy('covidcase.status_index_contact_tracing', $sortOrder);
                    break;
                case PlannerSort::priority():
                    $query->orderBy('covidcase.priority', $sortOrder);
                    break;
            }

            $query->orderBy('covidcase.date_of_test');
            $query->orderBy('covidcase.uuid'); // keeps the sort stable if sorted on a non-unique value
            return;
        }

        $view = $options->view;

        if ($view === PlannerView::unassigned() || $view === PlannerView::queued()) {
            $query->orderBy('covidcase.created_at');
        } elseif ($view === PlannerView::assigned() || $caseListUuid !== null) {
            $query->orderBy('covidcase.updated_at');
        } else { // completed and no case list
            $query->orderByDesc('covidcase.updated_at');
        }

        $query->orderBy('covidcase.date_of_test');
        $query->orderBy('covidcase.uuid');
    }

    private function addCaseListFilter(Builder $query, ?string $caseListUuid): void
    {
        if ($caseListUuid) {
            $query->where('covidcase.assigned_case_list_uuid', $caseListUuid);
        }
    }

    private function addCreatedByOrganisationFilter(Builder $query, ?string $organisation): void
    {
        if ($organisation) {
            $query->where('covidcase.organisation_uuid', $organisation);
        }
    }

    private function addAssignedUserFilter(Builder $query, ?string $userAssignment): void
    {
        if ($userAssignment) {
            $query->where('covidcase.assigned_user_uuid', $userAssignment);
        }
    }

    private function addStatusIndexContactTracingFilter(Builder $query, ?ContactTracingStatus $statusIndexContactTracing): void
    {
        if ($statusIndexContactTracing) {
            $query->where('covidcase.status_index_contact_tracing', $statusIndexContactTracing->value);
        }
    }

    private function addTestResultSourceFilter(Builder $query, ?TestResultSource $testResultSource): void
    {
        if ($testResultSource) {
            $query->whereHas('testResults', static function (Builder $query) use ($testResultSource): void {
                $query->where('source', '=', $testResultSource->value);
            });
        }
    }

    private function addAgeFilter(Builder $query, Range $range): void
    {
        $query->whereBetween('index_age', [$range->getMin(), $range->getMax()]);
    }

    private function addLabelFilter(Builder $query, ?string $label): void
    {
        if ($label) {
            $query->whereHas('caseLabels', static function (Builder $query) use ($label): void {
                $query->where('uuid', '=', $label);
            });
        }
    }

    private function addCurrentOrganisationFilter(Builder $query, EloquentOrganisation $organisation): void
    {
        $query->where('covidcase.current_organisation_uuid', $organisation->uuid);
    }

    private function addOrganisationFilter(Builder $query, EloquentOrganisation $organisation): void
    {
        $query->where('covidcase.organisation_uuid', $organisation->uuid);
    }

    private function addWasOutsourcedToOrganisationNameAttribute(Builder $query): void
    {
        $query->addSelect([new Expression("
            CASE WHEN covidcase.assigned_organisation_uuid IS NULL THEN (
                SELECT o.name
                FROM case_assignment_history h
                LEFT JOIN organisation o ON (o.uuid = h.assigned_organisation_uuid)
                WHERE h.covidcase_uuid = covidcase.uuid
                ORDER BY h.assigned_at DESC
                LIMIT 1, 1
            ) ELSE NULL END AS was_outsourced_to_organisation_name
        ")]);
    }

    private function addLastAssignedUserSelect(Builder $query): void
    {
        $query->selectRaw("
            CASE WHEN covidcase.assigned_user_uuid IS NULL THEN (
                SELECT bu.name
                FROM case_assignment_history cah
                INNER JOIN bcouser bu ON bu.uuid = cah.assigned_user_uuid
                WHERE cah.covidcase_uuid = covidcase.uuid
                ORDER BY cah.assigned_at DESC
                LIMIT 1
            ) END AS last_assigned_user_name
        ");
    }

    private function forceIndexForViewFilter(Builder $query, PlannerView $view, ?string $caseListUuid): void
    {
        // although the mysql optimizer should be able to determine the right index,
        // it sometimes doesn't and needs some help, this can make a big difference!
        $index = 'i_covidcase_planner_corg';

        if ($caseListUuid !== null) {
            $index = 'i_covidcase_planner_cl';
        }

        if ($view === PlannerView::outsourced()) {
            $index = 'i_covidcase_planner_org';
        }

        $query->fromRaw('covidcase USE INDEX (' . $index . ')');
    }

    public function getPlannerViewCount(EloquentOrganisation $organisation, PlannerView $view, ?string $caseListUuid, bool $forceFullCount = false): ?int
    {
        $query = EloquentCase::query()
            ->withoutGlobalScope(CaseAuthScope::class) // already set in the view filter
            ->withTrashed(); // planner view already filters out deleted records
        $this->addCaseListFilter($query, $caseListUuid);
        $this->addViewFilter($query, $organisation, $view, $caseListUuid);
        $this->forceIndexForViewFilter($query, $view, $caseListUuid);

        $fullCount = $forceFullCount || $this->config->get('misc.planner.case_count_limit') === 0;

        if (!$fullCount) {
            /** @var QueryBuilder $subQuery */
            $subQuery = $query->select(['covidcase.uuid'])
                ->limit((int) $this->config->get('misc.planner.case_count_limit') + 1);

            $query = EloquentCase::query()
                ->withoutGlobalScope(CaseAuthScope::class)
                ->withTrashed()
                ->fromSub($subQuery, 'covidcase');
        }

        $count = $query->count('covidcase.uuid');

        if (!$fullCount && $count > (int) $this->config->get('misc.planner.case_count_limit')) {
            $count = null;
        }

        return $count;
    }

    public function getPlannerViewCases(EloquentOrganisation $organisation, ListOptions $options): Paginator
    {
        $query = EloquentCase::query()
            ->select(['covidcase.uuid'])
            ->withoutGlobalScope(CaseAuthScope::class) // already set in the view filter
            ->withTrashed(); // planner view already filters out deleted records
        $this->addViewFilter($query, $organisation, $options->view, $options->caseListUuid);
        $this->addCaseListFilter($query, $options->caseListUuid);
        $this->addCreatedByOrganisationFilter($query, $options->organisation);
        $this->addLabelFilter($query, $options->label);
        $this->addAssignedUserFilter($query, $options->userAssignment);
        $this->addStatusIndexContactTracingFilter($query, $options->statusIndexContactTracing);
        $this->addTestResultSourceFilter($query, $options->testResultSource);
        if (is_int($options->minAge) && is_int($options->maxAge)) {
            $this->addAgeFilter($query, new Range($options->minAge, $options->maxAge));
        }
        $this->forceIndexForViewFilter($query, $options->view, $options->caseListUuid);
        $this->addViewOrderBy($query, $options, $options->caseListUuid);

        // We first only retrieve the UUIDs. We make sure that all the columns used for selecting and ordering
        // are part the index. This way when a temporary table is needed for a filesort or whatever the filesort
        // is really fast because all data is the index. If we would select all data the temporary table would
        // also contain all the fragment data etc. which makes it really slow.

        $uuids = $query
            ->limit($options->perPage + ($options->includeTotal ? 0 : 1))
            ->offset(($options->page - 1) * $options->perPage)
            ->pluck('uuid')
            ->all();

        if (count($uuids) > 0) {
            $query = EloquentCase::query()
                ->with(
                    ['organisation', 'assignedCaseList', 'assignedOrganisation', 'assignedUser', 'caseLabels', 'assignedUser.organisations', 'index'],
                )
                ->withoutGlobalScope(CaseAuthScope::class)
                ->whereIn('uuid', $uuids)
                ->withCount('tasks as contacts_count')
                ->withCount('notes as notes_count');
            $this->addWasOutsourcedToOrganisationNameAttribute($query);
            $this->addLastAssignedUserSelect($query);

            $rows = $query->get()
                ->sortBy(static fn (EloquentCase $row) => array_search($row->uuid, $uuids, true))
                ->values();
        } else {
            $rows = collect();
        }

        if ($options->includeTotal) {
            $total = (int) $this->getPlannerViewCount($organisation, $options->view, $options->caseListUuid, true);
            return new LengthAwarePaginatorImpl($rows, $total, $options->perPage, $options->page);
        }

        return new PaginatorImpl($rows, $options->perPage, $options->page);
    }

    /**
     * @throws BcoNumberException
     */
    public function createCase(CaseData $case): EloquentCase
    {
        /** @var EloquentCase $dbCase */
        $dbCase = EloquentCase::getSchema()->getCurrentVersion()->newInstance();

        $dbCase->owner = $case->owner;
        $dbCase->created_at ??= CarbonImmutable::now();
        $dbCase->updated_at ??= $dbCase->created_at;
        $dbCase->bco_status = BCOStatus::draft();
        $dbCase->index_status = IndexStatus::initial();
        $dbCase->bco_phase = $case->bcoPhase ?? BCOPhase::defaultItem();
        $dbCase->status_index_contact_tracing = $case->statusIndexContactTracing ?? ContactTracingStatus::defaultItem();
        $dbCase->organisation_uuid = $case->organisationUuid;
        $dbCase->case_id = $case->caseId; // note: probably null
        $dbCase->test_monster_number = $case->testMonsterNumber;
        $dbCase->assigned_user_uuid = $case->assignedUserUuid;
        $dbCase->assigned_case_list_uuid = $case->assignedCaseListUuid;
        $dbCase->status_explanation = "";
        $dbCase->organisation_label = $case->organisationLabel;
        $dbCase->pseudo_bsn_guid = $case->pseudoBsnGuid;
        $dbCase->priority = $case->priority;
        $dbCase->automatic_address_verification_status = $case->automaticAddressVerificationStatus;
        $dbCase->save();

        foreach ($case->caseLabels as $caseLabel) {
            $dbCase->caseLabels()->attach($caseLabel);
        }

        return $dbCase;
    }

    /**
     * @inheritDoc
     *
     * @deprecated Placeholder: No description was set at the time.
     */
    public function updateCase(object $case): bool
    {
        if ($case instanceof EloquentCase) {
            return $case->save();
        }

        $dbCase = $this->getCaseFromDb($case->uuid);
        $dbCase->case_id = $case->caseId;
        $dbCase->assigned_user_uuid = $case->assignedUserUuid;
        $dbCase->bco_status = $case->bcoStatus;
        $dbCase->is_approved = $case->isApproved;
        $dbCase->index_status = $case->indexStatus;
        $dbCase->copied_at = $case->copiedAt?->toDateTimeImmutable();
        $dbCase->exported_at = $case->exportedAt?->toDateTimeImmutable();
        $dbCase->export_id = $case->exportId;
        $dbCase->date_of_symptom_onset = $case->dateOfSymptomOnset?->toDateTimeImmutable();
        $dbCase->date_of_test = $case->dateOfTest?->toDateTimeImmutable();
        $dbCase->test_monster_number = $case->testMonsterNumber;
        $dbCase->symptomatic = $case->symptomatic !== null ? ($case->symptomatic ? 1 : 0) : null;
        $dbCase->status_index_contact_tracing = $case->statusIndexContactTracing;
        $dbCase->status_explanation = $case->statusExplanation;
        $dbCase->completed_at = $case->completedAt;
        $dbCase->pseudo_bsn_guid = $case->pseudoBsnGuid;
        $dbCase->organisation_label = $case->organisationLabel;
        $dbCase->assigned_organisation_label = $case->assignedOrganisationLabel;
        $dbCase->priority = $case->priority;

        $dbCase->caseLabels()->sync(collect($case->caseLabels)->pluck('uuid'));

        return $dbCase->save();
    }

    public function deleteCase(EloquentCase $case): void
    {
        $case->delete();
    }

    public function setExpiry(string $caseUuid, DateTimeImmutable $windowExpiresAt, DateTimeImmutable $pairingExpiresAt): mixed
    {
        $dbCase = $this->getCaseFromDb($caseUuid);
        $dbCase->window_expires_at = new CarbonImmutable($windowExpiresAt);
        $dbCase->pairing_expires_at = new CarbonImmutable($pairingExpiresAt);
        if ($dbCase->index_status === IndexStatus::timeout()) {
            $dbCase->index_status = IndexStatus::initial();
        }

        return $dbCase->save();
    }

    public function searchCases(array $conditions): Collection
    {
        return EloquentCase::withTrashed()
            ->where(static function ($query) use ($conditions): void {
                foreach ($conditions as $column => $value) {
                    $query = $query->where($column, '=', $value);
                }
            })->get();
    }

    public function getCasesByPseudoBsnGuid(string $pseudoBsnGuid, array $ignoreUuids = []): Collection
    {
        $query = EloquentCase::where('pseudo_bsn_guid', $pseudoBsnGuid)
            ->withoutGlobalScope(CaseAuthScope::class)
            ->with([
                'organisation' => static function ($query): void {
                        $query->withoutGlobalScope(OrganisationAuthScope::class);
                },
            ]);

        if (count($ignoreUuids) > 0) {
            $query = $query->whereNotIn('uuid', $ignoreUuids);
        }

        return $query
            ->orderby('created_at', 'desc')
            ->get();
    }

    public function caseFromEloquentModel(EloquentCase $dbCase): CovidCase
    {
        $case = new CovidCase();
        $case->uuid = $dbCase->uuid;
        $case->source = $dbCase->source;
        $case->caseId = $dbCase->case_id;
        $case->organisationUuid = $dbCase->organisation_uuid;
        $case->dateOfSymptomOnset = $dbCase->date_of_symptom_onset !== null ? new CarbonImmutable($dbCase->date_of_symptom_onset) : null;
        $case->dateOfTest = $dbCase->date_of_test !== null ? new CarbonImmutable($dbCase->date_of_test) : null;
        $case->testMonsterNumber = $dbCase->test_monster_number;
        $case->symptomatic = $dbCase->symptomatic !== null ? ($dbCase->symptomatic === 1) : null;
        $case->name = $dbCase->name;
        $case->owner = $dbCase->owner;
        $case->isApproved = $dbCase->is_approved;
        $case->bcoStatus = $dbCase->bco_status;
        $case->indexStatus = $dbCase->index_status ?? $case->indexStatus;
        $case->bcoPhase = $dbCase->bco_phase ?? $dbCase->organisation->bcoPhase;
        $case->assignedUserUuid = $dbCase->assigned_user_uuid;
        $case->assignedOrganisationUuid = $dbCase->assigned_organisation_uuid;
        $case->assignedCaseListUuid = $dbCase->assigned_case_list_uuid;
        $case->updatedAt = new CarbonImmutable($dbCase->updated_at);
        $case->createdAt = new CarbonImmutable($dbCase->created_at);
        $case->deletedAt = new CarbonImmutable($dbCase->deleted_at);
        $case->copiedAt = $dbCase->copied_at !== null ? new CarbonImmutable($dbCase->copied_at) : null;
        $case->exportedAt = $dbCase->exported_at !== null ? new CarbonImmutable($dbCase->exported_at) : null;
        $case->exportId = $dbCase->export_id;
        $case->pairingExpiresAt = $dbCase->pairing_expires_at !== null ? new CarbonImmutable($dbCase->pairing_expires_at) : null;
        $case->windowExpiresAt = $dbCase->window_expires_at !== null ? new CarbonImmutable($dbCase->window_expires_at) : null;
        $case->indexSubmittedAt = $dbCase->index_submitted_at !== null ? new CarbonImmutable($dbCase->index_submitted_at) : null;
        $case->statusIndexContactTracing = $dbCase->status_index_contact_tracing;
        $case->statusExplanation = $dbCase->status_explanation ?? $case->statusExplanation;
        $case->searchDateOfBirth = $dbCase->search_date_of_birth;
        $case->searchEmail = $dbCase->search_email;
        $case->searchPhone = $dbCase->search_phone;
        $case->completedAt = $dbCase->completed_at;
        $case->pseudoBsnGuid = $dbCase->pseudo_bsn_guid;
        $case->automaticAddressVerificationStatus = $dbCase->automatic_address_verification_status;
        $case->organisationLabel = $dbCase->organisation_label;
        $case->assignedOrganisationLabel = $dbCase->assigned_organisation_label;
        $case->schemaVersion = $dbCase->schema_version;
        $case->priority = $dbCase->priority ?? $case->priority;
        $case->caseLabels = $dbCase->caseLabels->all();

        if ($case->assignedUserUuid !== null) {
            $case->assignedName = $dbCase->assigned_name;
        }

        if ($dbCase->organisation !== null) {
            $case->organisation = $this->organisationRepository->getOrganisationFromEloquentModel($dbCase->organisation);
        } elseif ($case->organisationUuid !== null) {
            $case->organisation = $this->organisationRepository->getOrganisationByUuid($case->organisationUuid);
        }

        return $case;
    }

    public function getArchivedWithLabelsOrPriority(): Collection
    {
        $query = EloquentCase::query();
        $query->where('bco_status', BCOStatus::archived()->value);
        $query->where(static function (Builder $query): void {
            $query->where('priority', '!=', 0);
            $query->orHas('caseLabels');
        });
        return $query->get();
    }

    /**
     * @return Collection<int, EloquentCase>
     */
    public function getStaleCasesByBCOStatus(int $stalePeriodInDays, BCOStatus $bcoStatus): Collection
    {
        $query = EloquentCase::query();
        $query->where('bco_status', $bcoStatus->value);
        $query->where('updated_at', '<', CarbonImmutable::now()->subDays($stalePeriodInDays));
        return $query->get();
    }

    public function save(EloquentCase $eloquentCase): void
    {
        $eloquentCase->save();
    }

    public function saveQuietlyWithoutTimestamps(EloquentCase $eloquentCase): void
    {
        $eloquentCase->timestamps = false;
        $eloquentCase->saveQuietly();
        $eloquentCase->timestamps = true;
    }

    public function addCaseLabel(EloquentCase $case, CaseLabel $caseLabel): void
    {
        $case->caseLabels()->syncWithoutDetaching($caseLabel);
    }

    public function addCaseLabels(EloquentCase $case, Collection $caseLabels): void
    {
        $case->caseLabels()->sync($caseLabels->pluck('uuid'), false);
    }

    private function getCaseFromDb(string $caseUuid): EloquentCase
    {
        /** @var EloquentCase $case */
        $case = EloquentCase::where('uuid', $caseUuid)->first();

        Assert::notNull($case);

        return $case;
    }

    public function assignNextCase(string $caseListUuid, string $userUuid): ?string
    {
        return DB::transaction(static function () use ($caseListUuid, $userUuid) {
            for ($i = 0; $i < 5; $i++) {
                $case = DB::table('covidcase')
                    ->where('assigned_case_list_uuid', $caseListUuid)
                    ->whereNull('assigned_user_uuid')
                    ->orderByDesc('priority')
                    ->orderBy('covidcase.date_of_test')
                    ->orderBy('covidcase.created_at')
                    ->limit(1)
                    ->lockForUpdate()
                    ->first(['uuid']);

                if ($case !== null) {
                    break;
                }
            }

            if (!isset($case)) {
                return null;
            }

            $updated = DB::table('covidcase')
                ->where('uuid', $case->uuid)
                ->update([
                    'assigned_user_uuid' => $userUuid,
                    'assigned_case_list_uuid' => null,
                    'updated_at' => DB::raw('NOW()'),
                ]);

            return $updated === 1 ? $case->uuid : null;
        });
    }

    public function updateCaseIfUnchanged(EloquentCase $case): bool
    {
        $lockedCase = DB::table('covidcase')
            ->where('uuid', $case->uuid)
            ->lockForUpdate()
            ->first(['uuid', 'updated_at']);

        if (!$lockedCase) {
            return false;
        }

        $currentUpdatedAt = new CarbonImmutable($lockedCase->updated_at);
        if ($currentUpdatedAt->getTimestamp() !== $case->updatedAt->getTimestamp()) {
            return false; // updated
        }

        return $case->save();
    }

    public function updatePriorityForCases(array $caseUuids, Priority $priority): int
    {
        return EloquentCase::query()
            ->whereIn('uuid', $caseUuids)
            ->update([
                'priority' => $priority->value,
            ]);
    }

    public function openCase(EloquentCase $case): bool
    {
        $case->bcoStatus = BCOStatus::open();

        return $case->save();
    }

    public function reopenCase(EloquentCase $case): bool
    {
        $case->bcoStatus = BCOStatus::open();
        $case->isApproved = null;

        return $case->save();
    }

    public function giveCaseBackToWorkDistributor(EloquentCase $case): bool
    {
        $case->assigned_user_uuid = null;

        return $case->save();
    }

    public function markCaseComplete(EloquentCase $case, ?string $policyVersionUuid = null): bool
    {
        $case->bcoStatus = BCOStatus::completed();
        $case->completedAt = CarbonImmutable::now();
        $case->policyVersionUuid = $policyVersionUuid;

        return $case->save();
    }

    public function updateOrganisation(EloquentCase $case, string $organisationUuid): bool
    {
        foreach ($case->chores as $chore) {
            $this->choreService->updateOrganisation($chore->uuid, $organisationUuid);
        }

        // Set new organisation
        $case->organisation_uuid = $organisationUuid;

        // Reset any assignment
        $case->assigned_user_uuid = null;
        $case->assigned_case_list_uuid = null;
        $case->assigned_organisation_uuid = null;

        // Save the case
        return $case->save();
    }

    public function findCaseByPseudoBsnGuidCreatedAfter(string $pseudoBsnGuid, DateTimeInterface $createdAfterDate): ?EloquentCase
    {
        return EloquentCase::query()
            ->where('pseudo_bsn_guid', $pseudoBsnGuid)
            ->where('created_at', '>', $createdAfterDate)
            ->first();
    }

    private function getSearchColumn(CaseIdentifier $identifier): string
    {
        $searchColumn = 'test_monster_number';

        if ($identifier->isBcoPortalNumber()) {
            $searchColumn = 'case_id';
        } elseif ($identifier->isHpzoneNumber()) {
            $searchColumn = 'hpzone_number';
        }

        return $searchColumn;
    }

    public function getMutatedCasesForOrganisations(Collection $organisationIds, Cursor $cursor, int $limit): Collection
    {
        return $this->fetchMutationsHelper->fetchMutations(
            'covidcase',
            'i_covidcase_mutation',
            'updated_at',
            'deleted_at',
            $organisationIds,
            $cursor,
            $limit,
        );
    }

    public function unassignUserOnCase(EloquentCase $case): bool
    {
        $case->assigned_user_uuid = null;

        return $case->save();
    }

    public function archive(EloquentCase $case, ?string $policyVersionUuid = null): bool
    {
        if (!is_null($policyVersionUuid)) {
            $case->policyVersionUuid = $policyVersionUuid;
        }

        $case->bcoStatus = BCOStatus::archived();

        return $case->save();
    }

    public function unassignCaseListOnCase(EloquentCase $case): bool
    {
        $case->assigned_case_list_uuid = null;

        return $case->save();
    }

    public function setCaseApproval(EloquentCase $case, ?bool $isApproved): bool
    {
        $case->isApproved = $isApproved;

        return $case->save();
    }

    public function chunkCasesBetweenDates(
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $field,
        int $chunkSize,
        callable $callback,
    ): void {
        EloquentCase::query()
            ->whereBetween($field, [$start, $end])
            ->chunk($chunkSize, $callback);
    }

    public function setBcoPhaseForCase(EloquentCase $case, BCOPhase $bcoPhase): void
    {
        $case->bco_phase = $bcoPhase;
        $case->save();
    }

    public function markPairingRequestAccepted(EloquentCase $case): void
    {
        $case->indexStatus = IndexStatus::pairingRequestAccepted();
        $case->save();
    }
}
