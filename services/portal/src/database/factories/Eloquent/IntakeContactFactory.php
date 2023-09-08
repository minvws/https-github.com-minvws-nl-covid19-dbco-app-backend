<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Intake;
use App\Models\Eloquent\IntakeContact;
use Illuminate\Database\Eloquent\Factories\Factory;

class IntakeContactFactory extends Factory
{
    protected $model = IntakeContact::class;

    public function definition(): array
    {
        /** @var Intake $intake */
        $intake = Intake::factory()->create();
        return [
            'uuid' => $this->faker->uuid(),
            'intake_uuid' => $intake->uuid,
            'received_at' => $intake->received_at,
        ];
    }
}
