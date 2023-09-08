<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Assignment;
use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssignmentFactory extends Factory
{
    protected $model = Assignment::class;

    public function definition(): array
    {
        /** @var EloquentUser $user */
        $user = EloquentUser::factory()->create();

        /** @var Chore $chore */
        $chore = Chore::factory()->create();

        return [
            'uuid' => $this->faker->uuid(),
            'chore_uuid' => $chore->uuid,
            'user_uuid' => $user->uuid,
            'expires_at' => null,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'deleted_at' => null,
        ];
    }
}
