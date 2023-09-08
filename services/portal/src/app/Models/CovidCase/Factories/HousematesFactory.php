<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class HousematesFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hasHouseMates' => $this->faker->randomElement(YesNoUnknown::all()),
            'hasOwnFacilities' => $this->faker->boolean(),
            'hasOwnKitchen' => $this->faker->boolean(),
            'hasOwnBedroom' => $this->faker->boolean(),
            'hasOwnRestroom' => $this->faker->boolean(),
            'canStrictlyIsolate' => $this->faker->boolean(),
            'bottlenecks' => $this->faker->text(5000),
        ];
    }
}
