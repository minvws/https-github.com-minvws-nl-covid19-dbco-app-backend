<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentSituation;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentSituationFactory extends Factory
{
    protected $model = EloquentSituation::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->name(),
            'hpzone_number' => $this->faker->optional()->uuid(),
            'alarm' => $this->faker->optional()->word(),
            'snoozed_at' => $this->faker->optional()->dateTimeBetween(),
        ];
    }
}
