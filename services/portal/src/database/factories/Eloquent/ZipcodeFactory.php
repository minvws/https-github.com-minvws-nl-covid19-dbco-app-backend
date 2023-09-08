<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Zipcode;
use Illuminate\Database\Eloquent\Factories\Factory;

class ZipcodeFactory extends Factory
{
    protected $model = Zipcode::class;

    public function definition(): array
    {
        return [
            'zipcode' => $this->faker->postcode,
            'organisation_uuid' => static function () {
                return EloquentOrganisation::factory()->create();
            },
        ];
    }
}
