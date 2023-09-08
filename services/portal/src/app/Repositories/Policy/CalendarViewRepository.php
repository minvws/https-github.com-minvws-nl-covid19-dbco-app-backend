<?php

declare(strict_types=1);

namespace App\Repositories\Policy;

use App\Dto\Admin\UpdateCalendarViewDto;
use App\Models\Policy\CalendarView;
use Illuminate\Database\Eloquent\Collection;
use Webmozart\Assert\Assert;

final class CalendarViewRepository
{
    public function getCalendarView(string $calendarViewUuid): CalendarView
    {
        $calendarView = CalendarView::findOrFail($calendarViewUuid);

        Assert::isInstanceOf($calendarView, CalendarView::class);

        return $calendarView;
    }

    /**
     * @return Collection<CalendarView>
     */
    public function getCalendarViews(string $policyVersionUuid): Collection
    {
        return CalendarView::query()
            ->with('policyVersion')
            ->with('calendarItems')
            ->where('policy_version_uuid', $policyVersionUuid)
            ->get();
    }

    public function updateCalendarView(CalendarView $calendarView, UpdateCalendarViewDto $dto): CalendarView
    {
        if ($dto->label->isDefined()) {
            $calendarView->label = $dto->label->get();
        }

        if ($dto->calendarItems->isDefined()) {
            $calendarView->calendarItems()->sync($dto->calendarItems->get());
        }

        $calendarView->save();

        return $calendarView;
    }
}
