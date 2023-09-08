<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Place;
use App\Models\Eloquent\PlaceCounters;
use Illuminate\Database\Eloquent\Factories\Factory;

class PlaceCountersFactory extends Factory
{
    protected $model = PlaceCounters::class;

    public function definition(): array
    {
        return [
            'place_uuid' => static function () {
                return Place::factory()->create();
            },
            'index_count' => $this->faker->randomNumber(),
            'index_count_since_reset' => $this->faker->randomNumber(),
            'last_index_presence' => $this->faker->dateTime(),
        ];
    }
}
