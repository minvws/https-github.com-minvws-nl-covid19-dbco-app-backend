<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarItemConfig;
use App\Models\Policy\CalendarItemConfigStrategy;
use App\Models\Policy\DateOperation;
use App\Models\Policy\PolicyGuideline;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\CalendarItem as CalendarItemEnum;
use MinVWS\DBCO\Enum\Models\CalendarItemConfigStrategyIdentifierType;

/**
 * @extends Factory<CalendarItemConfig>
 */
class CalendarItemConfigFactory extends Factory
{
    protected $model = CalendarItemConfig::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'policy_guideline_uuid' => PolicyGuideline::factory(),
            'calendar_item_uuid' => CalendarItem::factory(),
            'is_hidden' => $this->faker->boolean(),
        ];
    }

    /**
     * @return Factory<CalendarItemConfig>
    */
    public function withStrategies(bool $withDateOperation = false, int $countDateOperation = 1): Factory
    {
        return $this->afterCreating(
            static function (CalendarItemConfig $calendarItemConfig) use ($withDateOperation, $countDateOperation): void {
                $calendarItem = $calendarItemConfig->calendarItem;

                $calendarItemConfigStrategySequence = $calendarItem->calendar_item_enum === CalendarItemEnum::point()
                    ? [
                        ['identifier_type' => CalendarItemConfigStrategyIdentifierType::point()],
                    ]
                    : [
                        ['identifier_type' => CalendarItemConfigStrategyIdentifierType::periodStart()],
                        ['identifier_type' => CalendarItemConfigStrategyIdentifierType::periodEnd()],
                    ];

                CalendarItemConfigStrategy::factory()
                    ->recycle($calendarItemConfig)
                    ->when(
                        $withDateOperation,
                        static fn (Factory $factory) => $factory->has(DateOperation::factory()->count($countDateOperation))
                    )
                    ->forEachSequence(...$calendarItemConfigStrategySequence)
                    ->create();
            },
        );
    }
}
