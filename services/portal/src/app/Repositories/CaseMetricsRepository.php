<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Dto\CasesCreatedArchivedMetric;
use App\Models\Eloquent\CaseMetrics;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Collection;

interface CaseMetricsRepository
{
    /**
     * Composes and returns a DTO containing the amount of created and archived cases for the given day.
     */
    public function getCreatedArchivedCountsForDate(
        CarbonInterface $date,
        string $organisationUuid,
    ): CasesCreatedArchivedMetric;

    /**
     * @return Collection<int,CaseMetrics>
     */
    public function getAllByOrganisationUuid(string $organisationUuid): Collection;

    public function getRefreshedAt(string $organisationUuid): ?DateTimeInterface;

    /**
     * @param Collection<int,CasesCreatedArchivedMetric> $caseMetricDtos
     */
    public function refreshByOrganisationUuid(Collection $caseMetricDtos, string $organisationUuid): void;

    public function create(string $organisationUuid, DateTimeInterface $date, int $createdCount, int $archivedCount, DateTimeInterface $refreshedAt): CaseMetrics;
}
