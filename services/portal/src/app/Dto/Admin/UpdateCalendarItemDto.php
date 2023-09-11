<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use PhpOption\Option;

final class UpdateCalendarItemDto
{
    /**
     * @param Option<string> $label
     * @param Option<CalendarPointColor|CalendarPeriodColor> $color
     */
    public function __construct(
        public readonly Option $label,
        public readonly Option $color,
    )
    {
    }

    public function toEloquentAttributes(): array
    {
        return Collection::make([
            'label' => $this->label,
            'color_enum' => $this->color,
        ])
            ->filter(static fn (Option $value): bool =>$value->isDefined())
            ->map(static fn (Option $value): mixed => $value->get())
            ->toArray();
    }
}
