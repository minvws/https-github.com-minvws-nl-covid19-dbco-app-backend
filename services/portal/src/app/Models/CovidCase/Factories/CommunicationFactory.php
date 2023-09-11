<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class CommunicationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'otherAdviceGiven' => $this->faker->text(5000),
            'particularities' => $this->faker->text(5000),
            'scientificResearchConsent' => $this->faker->randomElement(YesNoUnknown::all()),
            'remarksRivm' => $this->faker->text(5000),
        ];
    }
}
