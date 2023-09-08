<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\Moment;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class MomentFactory extends Factory
{
    protected $model = Moment::class;

    public function definition(): array
    {
        $date = CarbonImmutable::instance($this->faker->dateTime)->setHour(10);

        return [
            'uuid' => $this->faker->uuid(),
            'context_uuid' => static function () {
                return Context::factory()->create();
            },
            'day' => $date,
            'start_time' => $date->format('H:i'),
            'end_time' => $date->copy()->addHours(2)->format('H:i'),
        ];
    }
}
