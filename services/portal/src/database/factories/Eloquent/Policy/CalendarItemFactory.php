<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarPeriodColor;
use MinVWS\DBCO\Enum\Models\CalendarPointColor;
use MinVWS\DBCO\Enum\Models\FixedCalendarItem;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;

use function is_null;

/**
 * @extends Factory<CalendarItem>
 */
class CalendarItemFactory extends Factory
{
    protected $model = CalendarItem::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'policy_version_uuid' => PolicyVersion::factory(),
            'person_type_enum' => $this->faker->randomElement(PolicyPersonType::all()),
            'calendar_item_enum' => function (array $attributes) {
                if (is_null($attributes['color_enum'])) {
                    return $this->faker->randomElement(CalendarItemEnum::all());
                }

                if ($attributes['color_enum'] instanceof CalendarPointColor) {
                    return CalendarItemEnum::point();
                }

                return $this->faker->randomElement(CalendarItemEnum::all());
            },
            'label' => $this->faker->unique()->words(asText: true),
            'fixed_calendar_item_enum' => $this->faker->optional(0.3)->randomElement(FixedCalendarItem::all()),
            'color_enum' => function (array $attributes) {
                return match ($attributes['calendar_item_enum'] ?? null) {
                    CalendarItemEnum::point() => $this->faker->randomElement(CalendarPointColor::all()),
                    CalendarItemEnum::period() => $this->faker->randomElement(CalendarPeriodColor::all()),
                    default => $this->faker->randomElement([...CalendarPointColor::all(), ...CalendarPeriodColor::all()]),
                };
            },
        ];
    }

    /**
     * @return Factory<CalendarItem>
    */
    public function point(): Factory
    {
        return $this->state([
            'calendar_item_enum' => CalendarItemEnum::point(),
        ]);
    }

    /**
     * @return Factory<CalendarItem>
    */
    public function period(): Factory
    {
        return $this->state([
            'calendar_item_enum' => CalendarItemEnum::period(),
        ]);
    }

    /**
     * @return Factory<CalendarItem>
    */
    public function contact(): Factory
    {
        return $this->state([
            'person_type_enum' => PolicyPersonType::contact(),
        ]);
    }

    /**
     * @return Factory<CalendarItem>
    */
    public function index(): Factory
    {
        return $this->state([
            'person_type_enum' => PolicyPersonType::index(),
        ]);
    }
}
