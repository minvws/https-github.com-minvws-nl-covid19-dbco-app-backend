<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\BCOType;

class ExtensiveContactTracingFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'receivesExtensiveContactTracing' => $this->faker->randomElement(BCOType::all()),
            'otherDescription' => $this->faker->text(5000),
        ];
    }
}
