<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar\StrategyLoader;

use App\Models\Policy\CalendarItemConfigStrategy;
use Illuminate\Support\Collection;

final class NoOpStrategyLoader implements StrategyLoader
{
    public function get(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection
    {
        return Collection::make();
    }

    public function deleteAll(CalendarItemConfigStrategy $calendarItemConfigStrategy): void
    {
    }

    public function set(CalendarItemConfigStrategy $calendarItemConfigStrategy, Collection $data): Collection
    {
        return Collection::make();
    }

    public function getInitializeData(CalendarItemConfigStrategy $calendarItemConfigStrategy): Collection
    {
        return Collection::make();
    }
}
