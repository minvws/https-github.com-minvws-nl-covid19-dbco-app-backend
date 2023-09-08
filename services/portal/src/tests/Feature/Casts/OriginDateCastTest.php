<?php

declare(strict_types=1);

namespace Tests\Feature\Casts;

use App\Casts\OriginDateCast;
use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function sprintf;

#[Group('policy')]
#[Group('dateOperation')]
final class OriginDateCastTest extends FeatureTestCase
{
    public function testItReturnsValueAsIs(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->randomElement(IndexOriginDate::all());

        $result = $cast->get($dateOperation, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsTheValueAsIsOnNonString(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->boolean();

        $result = $cast->get($dateOperation, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testItReturnsIndexOriginDate(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $calendarItem = new CalendarItem([
            'person_type_enum' => PolicyPersonType::index(),
        ]);

        $dateOperation->setRelation(
            'calendarItemConfigStrategy',
            (new CalendarItemConfigStrategy())->setRelation(
                'calendarItemConfig',
                (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
            ),
        );

        $value = $this->faker->randomElement(IndexOriginDate::all());

        $result = $cast->get($dateOperation, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(IndexOriginDate::class, $value);
    }

    public function testItReturnsContactOriginDate(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $calendarItem = new CalendarItem([
            'person_type_enum' => PolicyPersonType::contact(),
        ]);

        $dateOperation->setRelation(
            'calendarItemConfigStrategy',
            (new CalendarItemConfigStrategy())->setRelation(
                'calendarItemConfig',
                (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
            ),
        );

        $value = $this->faker->randomElement(ContactOriginDate::all());

        $result = $cast->get($dateOperation, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(ContactOriginDate::class, $value);
    }

    public function testItReturnsIndexOriginDateWithoutGivingPersonType(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->randomElement(IndexOriginDate::all());

        $result = $cast->get($dateOperation, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(IndexOriginDate::class, $value);
    }

    public function testItReturnsContactOriginDateWithoutGivingPersonType(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->randomElement(ContactOriginDate::all());

        $result = $cast->get($dateOperation, $this->faker->word(), $value->value, []);

        $this->assertSame($value, $result);
        $this->assertInstanceOf(ContactOriginDate::class, $value);
    }

    public function testItThrowsExceptionWhenGivenInvalidValue(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $calendarItem = new CalendarItem([
            'person_type_enum' => PolicyPersonType::contact(),
        ]);

        $dateOperation->setRelation(
            'calendarItemConfigStrategy',
            (new CalendarItemConfigStrategy())->setRelation(
                'calendarItemConfig',
                (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
            ),
        );

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($dateOperation, $this->faker->word(), $value, []);
    }

    public function testItThrowsExceptionIfValueConflictsWithLoadedModel(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $calendarItem = new CalendarItem([
            'person_type_enum' => PolicyPersonType::contact(),
        ]);

        $dateOperation->setRelation(
            'calendarItemConfigStrategy',
            (new CalendarItemConfigStrategy())->setRelation(
                'calendarItemConfig',
                (new CalendarItemConfig())->setRelation('calendarItem', $calendarItem),
            ),
        );

        $value = $this->faker->randomElement(IndexOriginDate::all());

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value->value)));

        $cast->get($dateOperation, $this->faker->word(), $value->value, []);
    }

    public function testItThrowsExceptionWhenGivenInvalidValueWithoutGivingCalendarItemType(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->word();

        $this->expectExceptionObject(new InvalidArgumentException(sprintf('Invalid value "%s"', $value)));

        $cast->get($dateOperation, $this->faker->word(), $value, []);
    }

    public function testSettingNonEnumValue(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->word();

        $result = $cast->set($dateOperation, $this->faker->word(), $value, []);

        $this->assertSame($value, $result);
    }

    public function testSettingEnumValue(): void
    {
        $cast = new OriginDateCast();
        $dateOperation = new DateOperation();

        $value = $this->faker->randomElement(IndexOriginDate::all());

        $result = $cast->set($dateOperation, $this->faker->word(), $value, []);

        $this->assertSame($value->value, $result);
    }
}
