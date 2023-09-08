<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar\StrategyLoader;

use App\Models\Policy\CalendarItemConfigStrategy;
use Illuminate\Support\Collection;

/**
 * @template TData
 * @template TReturned
 */
interface StrategyLoader
{
    /**
     * @return Collection<array-key,TReturned>
     */
    public function get(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection;

    public function deleteAll(CalendarItemConfigStrategy $calendarItemConfigStrategy): void;

    /**
     * @param Collection<TData> $data
     *
     * @return Collection<array-key,TReturned>
     */
    public function set(CalendarItemConfigStrategy $calendarItemConfigStrategy, Collection $data): Collection;

    /**
     * @return Collection<array-key,TData>
     */
    public function getInitializeData(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection;
}
