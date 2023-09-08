<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Policy;

use App\Models\Policy\CalendarItem;
use App\Models\Policy\CalendarView;
use App\Models\Policy\PolicyVersion;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\CalendarView as CalendarViewEnum;

/**
 * @extends Factory<CalendarView>
 */
class CalendarViewFactory extends Factory
{
    protected $model = CalendarView::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'policy_version_uuid' => PolicyVersion::factory(),
            'label' => $this->faker->name(),
            'calendar_view_enum' => $this->faker->randomElement(CalendarViewEnum::all()),
        ];
    }

    public function withCalendarItems(PolicyVersion $policyVersion, array $attributes = [], int $count = 3,): Factory
    {
        return $this->has(
            CalendarItem::factory()
                ->recycle($policyVersion)
                ->state($attributes)
                ->count($count),
        );
    }
}
