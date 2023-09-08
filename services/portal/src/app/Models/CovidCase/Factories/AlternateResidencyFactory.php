<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Factories;

use App\Schema\Test\Factory;
use App\Schema\Types\SchemaType;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

class AlternateResidencyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'hasAlternateResidency' => $this->faker->randomElement(YesNoUnknown::all()),
            'remark' => $this->faker->text(),
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
