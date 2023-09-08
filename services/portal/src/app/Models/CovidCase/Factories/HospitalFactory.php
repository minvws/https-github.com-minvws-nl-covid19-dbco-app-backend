<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\HospitalReason;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class HospitalFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'isAdmitted' => $this->faker->optional()->randomElement(YesNoUnknown::all()),
            'name' => $this->faker->optional()->name(),
            'location' => $this->faker->optional()->sentence(),
            'admittedAt' => $this->faker->optional()->dateTimeBetween('-3 months'),
            'releasedAt' => $this->faker->optional()->dateTimeBetween('-3 months'),
            'reason' => $this->faker->optional()->randomElement(HospitalReason::all()),
            'hasGivenPermission' => $this->faker->optional()->randomElement(YesNoUnknown::all()),
            'practitioner' => $this->faker->optional()->sentence(),
            'practitionerPhone' => $this->faker->optional()->phoneNumber(),
            'isInICU' => $this->faker->optional()->randomElement(YesNoUnknown::all()),
            'admittedInICUAt' => $this->faker->optional()->dateTimeBetween('-3 months'),
        ];
    }
}
