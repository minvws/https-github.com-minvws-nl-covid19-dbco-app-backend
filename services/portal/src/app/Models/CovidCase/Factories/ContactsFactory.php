<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class ContactsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'shareNameWithContacts' => $this->faker->randomElement(['yes', 'no', 'specified']),
            'estimatedMissingContacts' => $this->faker->randomElement(YesNoUnknown::all()),
            'estimatedCategory1Contacts' => $this->faker->randomNumber(1),
            'estimatedCategory2Contacts' => $this->faker->randomNumber(1),
            'estimatedCategory3Contacts' => $this->faker->randomNumber(1),
        ];
    }
}
