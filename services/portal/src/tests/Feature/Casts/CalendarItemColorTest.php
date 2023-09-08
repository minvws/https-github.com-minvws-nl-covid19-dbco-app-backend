<?php

declare(strict_types=1);

namespace Tests\Feature\Casts;

use App\Casts\CalendarItemColor;
use App\Models\Policy\CalendarItem;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('calendarItem')]
final class CalendarItemColorTest extends FeatureTestCase
{
    public function testItReturnsValueAsIs(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->randomElement(CalendarPointColor::all());

        $result = $cast->get($calendarItem, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsTheValueAsIsOnNonString(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->boolean();

        $result = $cast->get($calendarItem, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsCalendarPointColor(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::point(),
        ]);

        $value = $this->faker->randomElement(CalendarPointColor::all());

        $result = $cast->get($calendarItem, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(CalendarPointColor::class, $value);
    }

    public function testItReturnsCalendarPeriodColor(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::period(),
        ]);

        $value = $this->faker->randomElement(CalendarPeriodColor::all());

        $result = $cast->get($calendarItem, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(CalendarPeriodColor::class, $value);
    }

    public function testItReturnsCalendarPointColorWithoutGivingCalendarItemType(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->randomElement(CalendarPointColor::all());

        $result = $cast->get($calendarItem, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(CalendarPointColor::class, $value);
    }

    public function testItReturnsCalendarPeriodColorWithoutGivingCalendarItemType(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->randomElement(CalendarPeriodColor::all());

        $result = $cast->get($calendarItem, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(CalendarPeriodColor::class, $value);
    }

    public function testItThrowsExceptionWhenGivenInvalidValue(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::period(),
        ]);

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($calendarItem, $this->faker->word(), $value, []);
    }

    public function testItThrowsExceptionWhenGivenInvalidValueWithoutGivingCalendarItemType(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($calendarItem, $this->faker->word(), $value, []);
    }

    public function testSettingNonEnumValue(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->word();

        $result = $cast->set($calendarItem, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testSettingEnumValue(): void
    {
        $cast = new CalendarItemColor();
        $calendarItem = new CalendarItem();

        $value = $this->faker->randomElement(CalendarPointColor::all());

        $result = $cast->set($calendarItem, $this->faker->word(), $value, []);

        $this->assertSame($value->value, $result);
    }
}
