<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Intake;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\Gender;
use MinVWS\DBCO\Enum\Models\IntakeType;
use Ramsey\Uuid\Uuid;

use function substr;

class IntakeFactory extends Factory
{
    protected $model = Intake::class;

    public function definition(): array
    {
        $createdAt = CarbonImmutable::now();

        return [
            'uuid' => $this->faker->uuid(),
            'organisation_uuid' => EloquentOrganisation::factory(),
            'type' => $this->faker->randomElement(IntakeType::all()),
            'source' => 'webportal',
            'identifier' => $this->faker->unique()->numberBetween(1_000_000, 9_999_999),
            'identifier_type' => 'testMonsterNumber',
            'pseudo_bsn_guid' => Uuid::uuid4(),
            'cat1_count' => $this->faker->numberBetween(0, 5),
            'estimated_cat2_count' => $this->faker->numberBetween(0, 5),
            'firstname' => $this->faker->optional()->firstName(),
            'prefix' => $this->faker->optional()->randomElement(['van', 'van de', 'de']),
            'lastname' => $this->faker->optional()->lastName(),
            'date_of_birth' => $this->faker->dateTimeBetween($createdAt->clone()->subYears(100), $createdAt->clone()->subYears(3)),
            'date_of_symptom_onset' => $this->faker->dateTimeBetween($createdAt->clone()->subDays(7), $createdAt->clone()->subDays(1)),
            'date_of_test' => $this->faker->dateTimeBetween($createdAt->clone()->subDays(7), $createdAt->clone()->subDays(1)),
            'created_at' => $createdAt,
            'received_at' => $createdAt,
            'pc3' => substr($this->faker->postcode, 0, 3),
            'gender' => $this->faker->randomElement(Gender::all()),
        ];
    }
}
