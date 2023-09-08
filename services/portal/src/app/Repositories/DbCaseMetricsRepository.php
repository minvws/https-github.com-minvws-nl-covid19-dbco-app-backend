<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\CasesCreatedArchivedMetric;
use App\Helpers\Config;
use App\Models\Eloquent\CaseMetrics;
use App\Models\Eloquent\CaseStatusHistory;
use App\Models\Eloquent\EloquentCase;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOStatus;

use function max;

class DbCaseMetricsRepository implements CaseMetricsRepository
{
    public function getCreatedArchivedCountsForDate(
        CarbonInterface $date,
        string $organisationUuid,
    ): CasesCreatedArchivedMetric {
        $dateDayAfter = CarbonImmutable::createFromInterface($date)
            ->modify('+1 day')
            ->modify('-1 second');

        return new CasesCreatedArchivedMetric(
            $date,
            $this->getCaseCountCreatedBetween($date, $dateDayAfter, $organisationUuid),
            $this->getCaseCountArchivedBetween($date, $dateDayAfter, $organisationUuid),
        );
    }

    public function getAllByOrganisationUuid(string $organisationUuid): Collection
    {
        return CaseMetrics::query()
            ->where('organisation_uuid', '=', $organisationUuid)
            ->get();
    }

    public function getRefreshedAt(string $organisationUuid): ?CarbonImmutable
    {
        /** @var ?CaseMetrics $caseMetrics */
        $caseMetrics = CaseMetrics::query()
            ->select(['refreshed_at'])
            ->firstWhere('organisation_uuid', '=', $organisationUuid);

        return $caseMetrics?->refreshed_at;
    }

    /**
     * @param Collection<int, CasesCreatedArchivedMetric> $caseMetricDtos
     */
    public function refreshByOrganisationUuid(Collection $caseMetricDtos, string $organisationUuid): void
    {
        $createdAt = CarbonImmutable::now();
        DB::transaction(function () use ($caseMetricDtos, $organisationUuid, $createdAt): void {
            CaseMetrics::query()
                ->where('organisation_uuid', '=', $organisationUuid)
                ->delete();

            foreach ($caseMetricDtos as $caseMetricDto) {
                $this->create(
                    $organisationUuid,
                    $caseMetricDto->date,
                    $caseMetricDto->created,
                    $caseMetricDto->archived,
                    $createdAt,
                );
            }
        });
    }

    public function create(string $organisationUuid, DateTimeInterface $date, int $createdCount, int $archivedCount, DateTimeInterface $refreshedAt): CaseMetrics
    {
        $caseMetric = new CaseMetrics();
        $caseMetric->organisation_uuid = $organisationUuid;
        $caseMetric->date = $date;
        $caseMetric->created_count = $createdCount;
        $caseMetric->archived_count = $archivedCount;
        $caseMetric->refreshed_at = $refreshedAt;
        $caseMetric->save();

        return $caseMetric;
    }

    private function getCaseCountCreatedBetween(
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $organisationUuid,
    ): int {
        return EloquentCase::query()
            ->withoutGlobalScopes()
            ->whereBetween('created_at', [$start, $end])
            ->where('organisation_uuid', $organisationUuid)
            ->count();
    }

    private function getCaseCountArchivedBetween(DateTimeInterface $start, DateTimeInterface $end, string $organisationUuid): int
    {
        $potentialCasesBaseQuery = $this->getArchivedCaseQueryUpdatedBetween($start, $end, $organisationUuid);
        if ($potentialCasesBaseQuery->clone()->count() === 0) {
            return 0;
        }

        return $this->sumBatchedCaseCountArchivedBetween($potentialCasesBaseQuery, static function (array $uuidBatch) use ($start, $end) {
            return EloquentCase::query()
                ->withoutGlobalScopes()
                ->join('case_status_history', static function (JoinClause $join) use ($uuidBatch): void {
                    $join->on('covidcase.uuid', '=', 'case_status_history.covidcase_uuid');
                    $join->on(static function (Builder $query) use ($uuidBatch): void {
                        $query->whereIn('case_status_history.covidcase_uuid', $uuidBatch);
                    });
                })
                ->joinSub(
                    CaseStatusHistory::query()
                        ->selectRaw('covidcase_uuid, MAX(changed_at) AS changed_at')
                        ->whereIn('covidcase_uuid', $uuidBatch)
                        ->groupBy('covidcase_uuid'),
                    'h2',
                    static function (JoinClause $join): void {
                        $join->on('case_status_history.covidcase_uuid', '=', 'h2.covidcase_uuid');
                        $join->on('case_status_history.changed_at', '=', 'h2.changed_at');
                    },
                )
                ->whereBetween('h2.changed_at', [$start, $end])
                ->count();
        });
    }

    /**
     * @param callable(array<int,string>):int $countQuery
     */
    private function sumBatchedCaseCountArchivedBetween(EloquentBuilder $caseQueryBuilder, callable $countQuery): int
    {
        $archivedCount = 0;
        $batchSize = Config::integer('casemetrics.archived_count_case_uuid_batch_size');

        $potentialCasesBaseQuery = $caseQueryBuilder->select(['uuid'])
            ->orderBy('uuid')
            ->limit(max(1, $batchSize));
        $potentialCasesQuery = $potentialCasesBaseQuery->clone();
        $potentialCases = $potentialCasesQuery->get('uuid');
        $uuidBatch = $potentialCases->pluck('uuid');

        while ($uuidBatch->count() > 0) {
            $cursor = $uuidBatch->last();
            /** @var array<int,string> $uuids */
            $uuids = $uuidBatch->all();

            $archivedCount += $countQuery($uuids);

            $uuidBatch = $potentialCasesBaseQuery->clone()
                ->where('uuid', '>', $cursor)
                ->get('uuid')
                ->pluck('uuid');
        }

        return $archivedCount;
    }

    private function getArchivedCaseQueryUpdatedBetween(
        DateTimeInterface $start,
        DateTimeInterface $end,
        string $organisationUuid,
    ): EloquentBuilder {
        $eloquentCaseBuilder = EloquentCase::query();

        return $eloquentCaseBuilder->withoutGlobalScopes()
            ->where('bco_status', BCOStatus::archived()->value)
            ->where('organisation_uuid', $organisationUuid)
            ->whereBetween('updated_at', [$start, $end]);
    }
}
