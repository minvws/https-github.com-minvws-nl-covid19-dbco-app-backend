<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Intake;
use App\Models\Eloquent\IntakeFragment;
use Illuminate\Database\Eloquent\Factories\Factory;
use stdClass;

class IntakeFragmentFactory extends Factory
{
    protected $model = IntakeFragment::class;

    public function definition(): array
    {
        return [
            'intake_uuid' => Intake::factory(),
            'name' => $this->faker->word(),
            'received_at' => $this->faker->dateTimeBetween('-14 days'),
            'data' => new stdClass(),
        ];
    }
}
