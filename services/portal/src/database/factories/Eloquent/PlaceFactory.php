<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Place;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\ContextCategory;

class PlaceFactory extends Factory
{
    protected $model = Place::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'label' => $this->faker->city,
            'location_id' => null,
            'category' => $this->faker->randomElement(ContextCategory::all()),
            'street' => $this->faker->streetName,
            'housenumber' => (string) $this->faker->randomNumber(),
            'postalcode' => $this->faker->postcode,
            'town' => $this->faker->city,
            'country' => 'NL',
            'is_verified' => $this->faker->boolean(),
            'schema_version' => Place::getSchema()->getCurrentVersion()->getVersion(),
        ];
    }
}
