<?php

declare(strict_types=1);

namespace App\Services\Calendar;

use App\Dto\Admin\UpdateCalendarViewDto;
use App\Models\Policy\CalendarView;
use App\Repositories\Policy\CalendarViewRepository;
use Illuminate\Database\Eloquent\Collection;

final class CalendarViewService
{
    public function __construct(private CalendarViewRepository $calendarViewRepository)
    {
    }

    /**
     * @return Collection<array-key,CalendarView>
     */
    public function getCalendarViews(string $policyVersionUuid): Collection
    {
        return $this->calendarViewRepository->getCalendarViews($policyVersionUuid);
    }

    public function updateCalendarView(CalendarView $calendarView, UpdateCalendarViewDto $dto): CalendarView
    {
        return $this->calendarViewRepository->updateCalendarView($calendarView, $dto);
    }
}
