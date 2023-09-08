<?php

declare(strict_types=1);

namespace App\Dto\Calendar;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;
use MinVWS\DBCO\Enum\Models\FixedCalendarPeriod;

class CalendarDataPeriodDto implements Arrayable
{
    private string $id;
    private CarbonInterface $startDate;
    private CarbonInterface $endDate;
    private ?FixedCalendarPeriod $key;
    private ?string $label;
    private ?string $color;

    public function __construct(
        string $id,
        CarbonInterface $startDate,
        CarbonInterface $endDate,
        ?FixedCalendarPeriod $key = null,
        ?string $label = null,
        ?string $color = null,
    ) {
        $this->id = $id;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->key = $key;
        $this->label = $label;
        $this->color = $color;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => 'period',
            'startDate' => $this->startDate->format('Y-m-d'),
            'endDate' => $this->endDate->format('Y-m-d'),
            'key' => $this->key,
            'label' => $this->label,
            'color' => $this->color,
        ];
    }
}
