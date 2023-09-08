<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Support\Collection;

use function array_filter;
use function count;

class DbContextRepository implements ContextRepository
{
    public function getContext(string $contextUuid): ?Context
    {
        return Context::find($contextUuid);
    }

    public function getContextsByCase(EloquentCase $case): Collection
    {
        return Context::where('covidcase_uuid', $case->uuid)->get();
    }

    public function getContextsByCaseWithRelationships(EloquentCase $case): Collection
    {
        return Context::with(['place', 'place.sections', 'moments', 'sections'])
            ->where('covidcase_uuid', $case->uuid)
            ->get();
    }

    public function getContextsByCaseAndDateRange(
        EloquentCase $case,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
    ): Collection {
        return Context::where('covidcase_uuid', $case->uuid)
            ->select('context.*') // https://github.com/laravel/framework/issues/4962
            ->join('moment', static function ($join) use ($startDate, $endDate): void {
                $join->on('context.uuid', '=', 'moment.context_uuid');

                if ($startDate !== null) {
                    $join->where('moment.day', '>=', $startDate->format('Y-m-d'));
                }

                if ($endDate !== null) {
                    $join->where('moment.day', '<=', $endDate->format('Y-m-d'));
                }
            })->get();
    }

    /**
     * Counts contexts linked to the given case that belong to a date range:
     *  - contexts with moment(s) within the date range are counted.
     *  - contexts without moments are also counted, because the application includes them in all date range groups.
     */
    public function countContextsByCaseAndDateRange(
        EloquentCase $case,
        ?DateTimeInterface $startDate,
        ?DateTimeInterface $endDate,
    ): int {
        $query = Context::query()
            ->where('covidcase_uuid', $case->uuid)
            ->join('moment', 'context.uuid', '=', 'moment.context_uuid', 'left')
            ->whereNull('moment.context_uuid');

        $startDateFilter = $startDate ? ['moment.day', '>=', $startDate->format('Y-m-d')] : null;
        $endDateFilter = $endDate ? ['moment.day', '<=', $endDate->format('Y-m-d')] : null;

        $where = array_filter([
            $startDateFilter,
            $endDateFilter,
        ]);

        if (count($where) !== 0) {
            $query->orWhere($where);
        }

        $query->distinct();
        return $query->count();
    }

    public function getIndexCountByPlace(Place $place, ?string $indexCountDateLimit = null): int
    {
        return Context::query()
            ->select('covidcase_uuid')
            ->where('place_uuid', $place->uuid)
            ->join('covidcase', static function (JoinClause $join) use ($indexCountDateLimit): void {
                $join->on('context.covidcase_uuid', '=', 'covidcase.uuid')
                    ->where('covidcase.episode_start_date', '>', $indexCountDateLimit);
            })
            ->distinct()
            ->count();
    }

    public function getIndexCountSinceResetByPlace(Place $place): int
    {
        return Context::query()
            ->select('covidcase_uuid')
            ->where('place_uuid', $place->uuid)
            ->where(static function (Builder $query) use ($place): void {
                if ($place->index_count_reset_at !== null) {
                    $query->where('place_added_at', '>', $place->index_count_reset_at);
                }
            })
            ->distinct()
            ->count();
    }
}
