<?php

declare(strict_types=1);

namespace App\Dto\Calendar;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Collection;

class CalendarResponseDto implements Arrayable
{
    /** @var Collection<CalendarDataPeriodDto> */
    private Collection $periods;

    /** @var Collection<CalendarDataPointDto> */
    private Collection $points;

    /** @var array<string, array> */
    private array $views;

    /**
     * @param Collection<CalendarDataPeriodDto> $periods
     * @param Collection<CalendarDataPointDto> $points
     * @param array<string, array> $views
     */
    public function __construct(
        Collection $periods,
        Collection $points,
        array $views,
    ) {
        $this->periods = $periods;
        $this->points = $points;
        $this->views = $views;
    }

    public function toArray(): array
    {
        return [
            'calendarData' => $this->periods->merge($this->points)->toArray(),
            'calendarViews' => $this->views,
        ];
    }
}
