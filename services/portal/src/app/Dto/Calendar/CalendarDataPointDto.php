<?php

declare(strict_types=1);

namespace App\Dto\Calendar;

use Carbon\CarbonInterface;
use Illuminate\Contracts\Support\Arrayable;

class CalendarDataPointDto implements Arrayable
{
    private string $id;
    private CarbonInterface $date;
    private ?string $label;
    private ?string $color;
    private ?string $icon;

    public function __construct(
        string $id,
        CarbonInterface $date,
        ?string $label = null,
        ?string $color = null,
        ?string $icon = null,
    ) {
        $this->id = $id;
        $this->date = $date;
        $this->label = $label;
        $this->color = $color;
        $this->icon = $icon;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => 'point',
            'startDate' => $this->date->format('Y-m-d'),
            'endDate' => $this->date->format('Y-m-d'),
            'label' => $this->label,
            'color' => $this->color,
            'icon' => $this->icon,
        ];
    }
}
