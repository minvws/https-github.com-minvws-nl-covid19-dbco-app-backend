<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Policy\CalendarItemConfigStrategy;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CalendarItemConfigStrategyUpdated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly CalendarItemConfigStrategy $calendarItemConfigStrategy)
    {
    }
}
