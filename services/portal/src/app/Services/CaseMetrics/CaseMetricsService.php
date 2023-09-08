<?php

declare(strict_types=1);

namespace App\Services\CaseMetrics;

use App\Dto\CasesCreatedArchivedMetric;
use App\Helpers\Config;
use App\Jobs\CaseMetricsRefreshJob;
use App\Models\Eloquent\CaseMetrics;
use App\Repositories\CaseMetricsRepository;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTimeInterface;
use Illuminate\Support\Collection;

use function array_reverse;
use function config;
use function md5;

class CaseMetricsService
{
    public function __construct(
        private readonly CaseMetricsRepository $caseMetricsRepository,
    ) {
    }

    public function queueRefreshForOrganisation(string $organisationUuid, CarbonInterface $periodEnd): void
    {
        /** @var string $connection */
        $connection = config('casemetrics.queue.connection');
        /** @var string $queueName */
        $queueName = config('casemetrics.queue.queue_name');

        CaseMetricsRefreshJob::dispatch($organisationUuid, $periodEnd)
            ->onConnection($connection)
            ->onQueue($queueName);
    }

    /**
     * @param CarbonInterface $periodEnd This date is used as end date for the period over which the metrics are calculated.
     *                                   The start date is derived from it using the case metrics config.
     */
    public function refreshForOrganisation(string $organisationUuid, CarbonInterface $periodEnd): void
    {
        $caseMetricDtos = $this->getCreatedArchivedCounts($organisationUuid, $periodEnd);
        $this->caseMetricsRepository->refreshByOrganisationUuid($caseMetricDtos, $organisationUuid);
    }

    /**
     * @return Collection<int,CasesCreatedArchivedMetric>
     */
    public function getCreatedArchivedMetrics(string $organisationUuid): Collection
    {
        return $this->caseMetricsRepository->getAllByOrganisationUuid($organisationUuid)
            ->map(
                static fn (CaseMetrics $model) => new CasesCreatedArchivedMetric(
                    $model->date,
                    $model->created_count,
                    $model->archived_count,
                    $model->refreshed_at,
                )
            );
    }

    public function getRefreshedAt(string $organisationUuid): ?DateTimeInterface
    {
        return $this->caseMetricsRepository->getRefreshedAt($organisationUuid);
    }

    public function getCreatedArchivedETag(DateTimeInterface $refreshedAt, string $organisationUuid): string
    {
        return md5("{$refreshedAt->getTimestamp()}_$organisationUuid");
    }

    /**
     * @return Collection<int, CasesCreatedArchivedMetric>
     */
    private function getCreatedArchivedCounts(string $organisationUuid, CarbonInterface $periodEnd): Collection
    {
        $numDaysInPast = Config::integer('casemetrics.created_archived_days_in_past');
        $periodStart = $periodEnd->avoidMutation()->subDays($numDaysInPast);

        $period = CarbonPeriod::between($periodStart, $periodEnd);
        $periodEndToStart = array_reverse($period->toArray());

        $metrics = new Collection();
        foreach ($periodEndToStart as $date) {
            $metrics->push($this->caseMetricsRepository->getCreatedArchivedCountsForDate($date, $organisationUuid));
        }

        return $metrics;
    }
}
