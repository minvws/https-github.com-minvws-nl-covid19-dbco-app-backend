<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\ContactOriginDate;
use MinVWS\DBCO\Enum\Models\DateOperationIdentifier;
use MinVWS\DBCO\Enum\Models\DateOperationMutation;
use MinVWS\DBCO\Enum\Models\IndexOriginDate;
use MinVWS\DBCO\Enum\Models\PolicyPersonType;
use MinVWS\DBCO\Enum\Models\UnitOfTime;
use RuntimeException;

/**
 * @extends Factory<DateOperation>
 */
class DateOperationFactory extends Factory
{
    protected $model = DateOperation::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'calendar_item_config_strategy_uuid' => CalendarItemConfigStrategy::factory(),
            'identifier_type' => $this->faker->randomElement(DateOperationIdentifier::all()),
            'mutation_type' => $this->faker->randomElement(DateOperationMutation::all()),
            'amount' => $this->faker->numberBetween(0, 15),
            'unit_of_time_type' => $this->faker->randomElement(UnitOfTime::all()),
            'origin_date_type' => function (array $attributes) {
                /** @var CalendarItemConfigStrategy $calendarItemConfigStrategy */
                $calendarItemConfigStrategy = CalendarItemConfigStrategy::query()
                    ->with('calendarItemConfig.calendarItem')
                    ->find($attributes['calendar_item_config_strategy_uuid']);

                $personType = $calendarItemConfigStrategy
                    ->calendarItemConfig
                    ->calendarItem
                    ->person_type_enum;

                return match ($personType) {
                    PolicyPersonType::index() => $this->faker->randomElement(IndexOriginDate::all()),
                    PolicyPersonType::contact() => $this->faker->randomElement(ContactOriginDate::all()),
                    default => throw new RuntimeException('Unknown person type'),
                };
            },
        ];
    }
}
