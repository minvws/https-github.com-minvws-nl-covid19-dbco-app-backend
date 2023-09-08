<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CalendarItemConfigStrategyUpdated;
use App\Services\CalendarItemConfigStrategyService;

final class UpdateCalendarItemConfigStrategies
{
    public function __construct(private readonly CalendarItemConfigStrategyService $calendarItemConfigStrategyService)
    {
    }

    public function handle(CalendarItemConfigStrategyUpdated $event): void
    {
        $calendarItemConfigStrategy = $event->calendarItemConfigStrategy;

        if ($calendarItemConfigStrategy->isClean('strategy_type')) {
            return;
        }

        $this->calendarItemConfigStrategyService->updateCalendarItemConfigStrategies($event->calendarItemConfigStrategy);
    }
}
