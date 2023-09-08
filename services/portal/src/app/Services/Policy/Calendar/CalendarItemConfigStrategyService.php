<?php

declare(strict_types=1);

namespace App\Services\Policy\Calendar;

use App\Dto\Admin\UpdateCalendarItemConfigStrategyDto;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Repositories\Policy\CalendarItemConfigStrategyRepository;

final class CalendarItemConfigStrategyService
{
    public function __construct(private CalendarItemConfigStrategyRepository $calendarItemConfigStrategyRepository)
    {
    }

    public function updateCalendarItemConfigStrategy(CalendarItemConfigStrategy $calendarItemConfigStrategy, UpdateCalendarItemConfigStrategyDto $dto): CalendarItemConfigStrategy
    {
        return $this->calendarItemConfigStrategyRepository->updateCalendarItemConfigStrategy($calendarItemConfigStrategy, $dto);
    }
}
