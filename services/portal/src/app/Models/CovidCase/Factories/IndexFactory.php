<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use App\Schema\Types\SchemaType;

class IndexFactory extends Factory
{
    protected function definition(): array
    {
        return [
            'firstname' => $this->faker->firstName(),
            'lastname' => $this->faker->lastName(),
            'dateOfBirth' => $this->faker->dateTimeBetween('-90 years', '-4 years'),
            'gender' => $this->randomOptionForEnumField('gender'),
            'address' => $this
                ->getSchemaVersion()
                ->getExpectedField('address')
                ->getExpectedType(SchemaType::class)
                ->getSchemaVersion()
                ->getTestFactory()
                ->make(),
        ];
    }
}
