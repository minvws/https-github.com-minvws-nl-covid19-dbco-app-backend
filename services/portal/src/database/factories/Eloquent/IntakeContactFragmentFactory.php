<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\IntakeContact;
use App\Models\Eloquent\IntakeContactFragment;
use Illuminate\Database\Eloquent\Factories\Factory;
use stdClass;

class IntakeContactFragmentFactory extends Factory
{
    protected $model = IntakeContactFragment::class;

    public function definition(): array
    {
        return [
            'intake_contact_uuid' => IntakeContact::factory(),
            'name' => $this->faker->word(),
            'received_at' => $this->faker->dateTimeBetween('-2 weeks'),
            'data' => new stdClass(),
            'version' => 1,
        ];
    }
}
