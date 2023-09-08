<?php

declare(strict_types=1);

namespace Tests\Unit\Dto\Admin;

use App\Dto\Admin\CreateCalendarItemDto;
use App\Repositories\Policy\PopulatorReferenceEnum;
use MinVWS\DBCO\Enum\Models\CalendarItem;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Unit\UnitTestCase;

#[Group('policy')]
#[Group('calendarItem')]
final class CreateCalendarItemDtoTest extends UnitTestCase
{
    public function testItCanBeInitialized(): void
    {
        $dto = new CreateCalendarItemDto(
            label: $this->faker->word(),
            personType: $this->faker->randomElement(PolicyPersonType::all()),
            itemType: $this->faker->randomElement(CalendarItem::all()),
            color: $this->faker->randomElement([...CalendarPointColor::all(), ...CalendarPeriodColor::all()]),
            fixedCalendarItemType: $this->faker->randomElement([...FixedCalendarItem::all(), null]),
            populatorReferenceEnum: $this->faker->randomElement([...PopulatorReferenceEnum::cases(), null]),
        );

        $this->assertInstanceOf(CreateCalendarItemDto::class, $dto);
    }

    public function testToEloquentAttributes(): void
    {
        $dto = new CreateCalendarItemDto(
            label: $label = $this->faker->word(),
            personType: $personType = $this->faker->randomElement(PolicyPersonType::all()),
            itemType: $itemType = $this->faker->randomElement(CalendarItem::all()),
            color: $color = $this->faker->randomElement([...CalendarPointColor::all(), ...CalendarPeriodColor::all()]),
            fixedCalendarItemType: $fixedCalendarItemType = $this->faker->randomElement([...FixedCalendarItem::all(), null]),
            populatorReferenceEnum: $populatorReferenceEnum = $this->faker->randomElement([...PopulatorReferenceEnum::cases(), null]),
        );

        $this->assertEqualsCanonicalizing([
            'label' => $label,
            'person_type_enum' => $personType,
            'calendar_item_enum' => $itemType,
            'color_enum' => $color,
            'fixed_calendar_item_enum' => $fixedCalendarItemType,
            'populatorReferenceEnum' => $populatorReferenceEnum,
        ], $dto->toEloquentAttributes());
    }
}
