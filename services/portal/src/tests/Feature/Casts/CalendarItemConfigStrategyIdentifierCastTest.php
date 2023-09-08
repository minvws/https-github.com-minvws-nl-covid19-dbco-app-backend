<?php

declare(strict_types=1);

namespace Tests\Feature\Casts;

use App\Casts\CalendarItemConfigStrategyIdentifierCast;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\PeriodCalendarStrategyType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('calendarItemConfigStrategy')]
final class CalendarItemConfigStrategyIdentifierCastTest extends FeatureTestCase
{
    public function testItReturnsValueAsIs(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->randomElement(PointCalendarStrategyType::all());

        $result = $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsTheValueAsIsOnNonString(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->boolean();

        $result = $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsPointCalendarStrategyType(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::point(),
        ]);

        $calendarItemConfigStrategy->setRelation(
            'calendarItemConfig',
            (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
        );

        $value = $this->faker->randomElement(PointCalendarStrategyType::all());

        $result = $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(PointCalendarStrategyType::class, $value);
    }

    public function testItReturnsPeriodCalendarStrategyType(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::period(),
        ]);

        $calendarItemConfigStrategy->setRelation(
            'calendarItemConfig',
            (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
        );

        $value = $this->faker->randomElement(PeriodCalendarStrategyType::all());

        $result = $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(PeriodCalendarStrategyType::class, $value);
    }

    public function testItReturnsPointCalendarStrategTypeWithoutGivingPersonType(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->randomElement(PointCalendarStrategyType::all());

        $result = $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(PointCalendarStrategyType::class, $value);
    }

    public function testItReturnsPeriodCalendarStrategyTypeWithoutGivingPersonType(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->randomElement(PeriodCalendarStrategyType::all());

        $result = $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(PeriodCalendarStrategyType::class, $value);
    }

    public function testItThrowsExceptionWhenGivenInvalidValue(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::period(),
        ]);

        $calendarItemConfigStrategy->setRelation(
            'calendarItemConfig',
            (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
        );

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value, []);
    }

    public function testItThrowsExceptionIfValueConflictsWithLoadedModel(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $calendarItem = new CalendarItem([
            'calendar_item_enum' => CalendarItemEnum::period(),
        ]);

        $calendarItemConfigStrategy->setRelation(
            'calendarItemConfig',
            (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
        );

        $value = $this->faker->randomElement(PointCalendarStrategyType::all());

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value->value)));

        $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value->value, []);
    }

    public function testItThrowsExceptionWhenGivenInvalidValueWithoutGivingCalendarItemType(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($calendarItemConfigStrategy, $this->faker->word(), $value, []);
    }

    public function testSettingNonEnumValue(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->word();

        $result = $cast->set($calendarItemConfigStrategy, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testSettingEnumValue(): void
    {
        $cast = new CalendarItemConfigStrategyIdentifierCast();
        $calendarItemConfigStrategy = new CalendarItemConfigStrategy();

        $value = $this->faker->randomElement(PointCalendarStrategyType::all());

        $result = $cast->set($calendarItemConfigStrategy, $this->faker->word(), $value, []);

        $this->assertSame($value->value, $result);
    }
}
