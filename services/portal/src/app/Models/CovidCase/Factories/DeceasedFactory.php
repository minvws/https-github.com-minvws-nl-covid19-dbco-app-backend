<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\CauseOfDeath;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class DeceasedFactory extends Factory
{
    public function definition(): array
    {
        $deceased = $this->faker->randomElement(YesNoUnknown::all());
        $deceasedAt = $deceased === YesNoUnknown::yes() ? $this->faker->dateTime() : null;
        $cause = $deceased === YesNoUnknown::yes() ? $this->faker->randomElement(CauseOfDeath::all()) : null;

        return [
            'isDeceased' => $deceased,
            'deceasedAt' => $deceasedAt,
            'cause' => $cause,
        ];
    }
}
