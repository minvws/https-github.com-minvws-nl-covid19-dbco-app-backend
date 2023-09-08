<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\ProfessionCare;
use MinVWS\DBCO\Enum\Models\ProfessionOther;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function count;

class JobFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'wasAtJob' => $this->faker->randomElement(YesNoUnknown::all()),
            'sectors' => $this->faker->randomElements(
                JobSector::all(),
                $this->faker->numberBetween(0, count(JobSector::all())),
            ),
            'professionCare' => $this->faker->randomElement(ProfessionCare::all()),
            'closeContactAtJob' => $this->faker->randomElement(YesNoUnknown::all()),
            'professionOther' => $this->faker->randomElement(ProfessionOther::all()),
            'otherProfession' => $this->faker->optional()->sentence(),
            'particularities' => $this->faker->optional()->paragraph(),
        ];
    }
}
