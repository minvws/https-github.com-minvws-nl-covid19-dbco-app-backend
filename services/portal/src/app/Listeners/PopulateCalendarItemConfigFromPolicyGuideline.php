<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\PolicyGuidelineCreated;
use App\Services\CalendarItemConfigService;

final class PopulateCalendarItemConfigFromPolicyGuideline
{
    public function __construct(private readonly CalendarItemConfigService $calendarItemConfigService)
    {
    }

    public function handle(PolicyGuidelineCreated $event): void
    {
        $this->calendarItemConfigService->createDefaultCalendarItemConfigsForNewPolicyGuideline($event->policyGuideline);
    }
}
