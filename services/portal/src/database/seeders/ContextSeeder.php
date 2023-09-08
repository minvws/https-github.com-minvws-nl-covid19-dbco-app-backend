<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Moment;
use App\Models\Eloquent\Place;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Tests\Faker\FakerFactory;

class ContextSeeder extends Seeder
{
    private const NUMBER_OF_CASES = 2000;

    protected Generator $faker;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = $this->getFaker();
        $orgUuid = '00000000-0000-0000-0000-000000000000';
        $userUuid = '00000000-0000-0000-0000-200000000002';

        for ($i = 0; $i < self::NUMBER_OF_CASES; $i++) {
            /** @var Place $place */
            $place = Place::factory()->create(['organisation_uuid' => $orgUuid]);
            $placeUuid = $place->uuid;

            /** @var callable(array<string, mixed>, ?Model): array<string, mixed> $momentState */
            $momentState = static fn (array $attributes, ?Context $context): array
                => ['day' => $faker->dateTimeBetween('-2 weeks')->format('Y-m-d')];

            EloquentCase::factory()
                ->withFragments()
                ->has(
                    Context::factory()
                        ->count($this->faker->numberBetween(10, 20))
                        ->has(
                            Moment::factory()
                                ->count($this->faker->numberBetween(10, 30))
                                ->state($momentState),
                        )
                        ->state(['place_uuid' => $placeUuid]),
                )
                ->create([
                    'organisation_uuid' => $orgUuid,
                    'assigned_user_uuid' => $userUuid,
                ]);
        }
    }

    private function getFaker(): Generator
    {
        if (isset($this->faker)) {
            return $this->faker;
        }

        return $this->faker = FakerFactory::addProviders(Factory::create('nl_NL'));
    }
}
