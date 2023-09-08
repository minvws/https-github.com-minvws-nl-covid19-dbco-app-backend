<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\CalendarItemCreated;
use App\Services\CalendarItemConfigService;

final class PopulateCalendarItemConfigFromCalendarItem
{
    public function __construct(private readonly CalendarItemConfigService $calendarItemConfigService)
    {
    }

    public function handle(CalendarItemCreated $event): void
    {
        $this->calendarItemConfigService->createDefaultCalendarItemConfigsForNewCalendarItem($event->calendarItem);
    }
}
