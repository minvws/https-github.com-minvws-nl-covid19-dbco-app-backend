<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Models\CovidCase\Abroad;
use App\Schema\Test\Factory;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;

use function random_int;

/**
 * @extends Factory<Abroad>
 */
class AbroadFactory extends Factory
{
    protected function definition(): array
    {
        $wasAbroad = $this->randomOptionForEnumField('wasAbroad');

        $trips = [];
        for ($i = 0; $i < random_int(0, 5); $i++) {
            $trips[] = $this->getSchemaVersion()
                ->getExpectedField('trips')
                ->getExpectedType(ArrayType::class)
                ->getExpectedElementType(SchemaType::class)
                ->getSchemaVersion()
                ->getTestFactory()
                ->make();
        }

        return [
            'wasAbroad' => $wasAbroad,
            'trips' => $trips,
        ];
    }
}
