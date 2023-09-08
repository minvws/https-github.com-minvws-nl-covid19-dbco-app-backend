<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;

use function array_fill;
use function explode;

class PrincipalContextualSettingsFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'hasPrincipalContextualSettings' => $this->faker->boolean(),
            'items' => explode(' ', $this->faker->sentence()),
            'otherItems' => array_fill(0, $this->faker->numberBetween(0, 6), $this->faker->word()),
        ];
    }
}
