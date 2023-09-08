<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

interface ContextRepository
{
    /**
     * Returns the context
     */
    public function getContext(string $contextUuid): ?Context;

    /**
     * Returns all the contexts for a case
     *
     * @return Collection<Context>
     */
    public function getContextsByCase(EloquentCase $case): Collection;

    /**
     * Returns all the contexts for a case with connected underlying relationships
     * Relationships: place -> sections, moments
     *
     * @return Collection<Context>
     */
    public function getContextsByCaseWithRelationships(EloquentCase $case): Collection;

    /**
     * Returns context for a case with moments between dates
     *
     * @return Collection<Context>
     */
    public function getContextsByCaseAndDateRange(
        EloquentCase $case,
        ?CarbonInterface $startDate,
        ?CarbonInterface $endDate,
    ): Collection;

    public function countContextsByCaseAndDateRange(
        EloquentCase $case,
        ?CarbonInterface $startDate,
        ?CarbonInterface $endDate,
    ): int;

    public function getIndexCountByPlace(Place $place, ?string $indexCountDateLimit = null): int;

    public function getIndexCountSinceResetByPlace(Place $place): int;
}
