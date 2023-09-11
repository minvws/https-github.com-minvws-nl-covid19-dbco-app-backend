<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\RiskLocationType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class RiskLocationFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'isLivingAtRiskLocation' => $this->faker->randomElement(YesNoUnknown::all()),
            'type' => $this->faker->randomElement(RiskLocationType::all()),
            'otherType' => $this->faker->optional()->paragraph(),
        ];
    }
}
