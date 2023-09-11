<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentCaseLock;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentCaseLockFactory extends Factory
{
    protected $model = EloquentCaseLock::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'case_uuid' => static fn() => EloquentCase::factory()->create(),
            'user_uuid' => static fn() => EloquentUser::factory()->create(),
            'locked_at' => CarbonImmutable::now(),
        ];
    }
}
