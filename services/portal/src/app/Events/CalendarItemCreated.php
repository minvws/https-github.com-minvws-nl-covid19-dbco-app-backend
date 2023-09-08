<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\Policy\CalendarItem;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CalendarItemCreated
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public readonly CalendarItem $calendarItem)
    {
    }
}
