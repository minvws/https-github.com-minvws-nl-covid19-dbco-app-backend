<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;

class ContactFactory extends Factory
{
    public function definition(): array
    {
        return [
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->email(),
        ];
    }
}
