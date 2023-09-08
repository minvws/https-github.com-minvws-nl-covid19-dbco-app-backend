<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CalendarItemConfigStrategyCreated;
use App\Services\CalendarItemConfigStrategyService;

final class InitializeCalendarItemConfigStrategies
{
    public function __construct(private readonly CalendarItemConfigStrategyService $calendarItemConfigStrategyService)
    {
    }

    public function handle(CalendarItemConfigStrategyCreated $event): void
    {
        $this->calendarItemConfigStrategyService->initializeCalendarItemConfigStrategies($event->calendarItemConfigStrategy);
    }
}
