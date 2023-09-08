<?php

declare(strict_types=1);

namespace App\Dto\Admin;

use App\Repositories\Policy\PopulatorReferenceEnum;
use MinVWS\DBCO\Enum\Models\CalendarItem;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

final class CreateCalendarItemDto
{
    public function __construct(
        public readonly string $label,
        public readonly PolicyPersonType $personType,
        public readonly CalendarItem $itemType,
        public readonly CalendarPointColor|CalendarPeriodColor $color,
        public readonly ?FixedCalendarItem $fixedCalendarItemType = null,
        public readonly ?PopulatorReferenceEnum $populatorReferenceEnum = null,
    )
    {
    }

    public function toEloquentAttributes(): array
    {
        return [
            'label' => $this->label,
            'person_type_enum' => $this->personType,
            'calendar_item_enum' => $this->itemType,
            'color_enum' => $this->color,
            'fixed_calendar_item_enum' => $this->fixedCalendarItemType,
            'populator_reference_enum' => $this->populatorReferenceEnum,
        ];
    }
}
