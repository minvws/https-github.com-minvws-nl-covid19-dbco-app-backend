<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\Relationship;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class AlternateContactFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'hasAlternateContact' => $this->faker->randomElement(YesNoUnknown::all()),
            'gender' => $this->faker->randomElement(Gender::all()),
            'relationship' => $this->faker->randomElement(Relationship::all()),
            'firstname' => $this->faker->optional()->firstName(),
            'lastname' => $this->faker->optional()->lastName(),
            'phone' => $this->faker->optional()->phoneNumber(),
            'email' => $this->faker->optional()->safeEmail(),
            'isDefaultContact' => $this->faker->boolean(),
        ];
    }
}
