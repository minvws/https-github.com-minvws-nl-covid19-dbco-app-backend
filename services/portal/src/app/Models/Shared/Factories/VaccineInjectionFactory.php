<?php

declare(strict_types=1);

namespace App\Models\Shared\Factories;

use App\Models\Shared\VaccineInjection;
use App\Schema\Test\Factory;
use MinVWS\DBCO\Enum\Models\Vaccine;

/**
 * @extends Factory<VaccineInjection>
 */
class VaccineInjectionFactory extends Factory
{
    protected function definition(): array
    {
        $type = $this->faker->boolean(80) ? $this->randomOptionForEnumField('vaccineType') : null;

        $otherType = null;
        if ($type === Vaccine::other()) {
            $otherType = $this->faker->optional()->text(100);
        }

        return [
            'vaccineType' => $type,
            'otherVaccineType' => $otherType,
            'injectionDate' => $this->faker->optional()->dateTimeBetween('6 months ago', '2 weeks ago'),
            'isInjectionDateEstimated' => $this->faker->optional()->boolean(),
        ];
    }
}
