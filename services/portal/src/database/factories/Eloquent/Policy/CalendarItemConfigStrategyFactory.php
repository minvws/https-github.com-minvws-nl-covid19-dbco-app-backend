<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarItemConfigStrategyIdentifierType;
use MinVWS\DBCO\Enum\Models\PointCalendarStrategyType;
use RuntimeException;

/**
 * @extends Factory<CalendarItemConfigStrategy>
 */
class CalendarItemConfigStrategyFactory extends Factory
{
    protected $model = CalendarItemConfigStrategy::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'calendar_item_config_uuid' => CalendarItemConfig::factory(),
            'identifier_type' => $this->faker->randomElement(CalendarItemConfigStrategyIdentifierType::all()),
            'strategy_type' => function (array $attributes) {
                /** @var CalendarItemConfig $calendarItemConfig */
                $calendarItemConfig = CalendarItemConfig::query()
                    ->with('calendarItem')
                    ->find($attributes['calendar_item_config_uuid']);

                $calendarItemEnum = $calendarItemConfig->calendarItem->calendar_item_enum;

                return match ($calendarItemEnum) {
                    CalendarItemEnum::point() => $this->faker->randomElement(PointCalendarStrategyType::all()),
                    CalendarItemEnum::period() => $this->faker->randomElement(PointCalendarStrategyType::all()),
                    default => throw new RuntimeException('Unknown calendar item type'),
                };
            },

        ];
    }
}
