<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\OrganisationType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentOrganisationFactory extends Factory
{
    protected $model = EloquentOrganisation::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'external_id' => $this->faker->uuid(),
            'type' => $this->faker->randomElement(OrganisationType::allValues()),
            'hp_zone_code' => (string) $this->faker->randomNumber(7),
            'name' => $this->faker->company(),
            'phone_number' => $this->faker->optional()->phoneNumber(),
        ];
    }
}
