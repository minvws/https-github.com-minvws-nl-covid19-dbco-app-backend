<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Chore;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentTask;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class ChoreFactory extends Factory
{
    protected $model = Chore::class;

    public function definition(): array
    {
        /** @var EloquentCase $resource */
        $resource = EloquentCase::factory()->create();

        /** @var EloquentTask $resourceOwner */
        $resourceOwner = EloquentTask::factory()->create();

        /** @var EloquentOrganisation $organisation */
        $organisation = EloquentOrganisation::factory()->create();

        $now = CarbonImmutable::now();
        return [
            'uuid' => $this->faker->uuid(),
            'resource_type' => 'covid-case',
            'resource_id' => $resource->uuid,
            'resource_permission' => 'view',
            'owner_resource_type' => 'task',
            'owner_resource_id' => $resourceOwner->uuid,
            'organisation_uuid' => $organisation->uuid,
            'expires_at' => $now->addMonth(),
            'created_at' => $now,
            'updated_at' => $now,
            'deleted_at' => null,
        ];
    }
}
