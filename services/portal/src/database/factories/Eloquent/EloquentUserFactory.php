<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

use function collect;
use function config;

class EloquentUserFactory extends Factory
{
    protected $model = EloquentUser::class;

    public function definition(): array
    {
        $existingRoles = collect(config('permissions'))->keys();
        $randomRoles = $this->faker->randomElements(
            $existingRoles->all(),
            $this->faker->numberBetween(1, $existingRoles->count()),
        );

        do {
            $externalId = $this->faker->unique()->randomNumber(6);
        } while (EloquentUser::query()->where('external_id', $externalId)->count('uuid') > 0);

        return [
            'uuid' => $this->faker->uuid(),
            'last_login_at' => CarbonImmutable::now()->modify('-30 minutes'),
            'name' => $this->faker->firstName() . ' ' . $this->faker->lastName(),
            'external_id' => (string) $externalId,
            'roles' => collect($randomRoles)->implode(','),
            'consented_at' => $this->faker->dateTime(),
        ];
    }
}
