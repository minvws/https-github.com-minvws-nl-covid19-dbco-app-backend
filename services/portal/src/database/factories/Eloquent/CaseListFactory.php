<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentOrganisation;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseListFactory extends Factory
{
    protected $model = CaseList::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => 'Lijst ' . $this->faker->word(),
            'organisation_uuid' => static function () {
                return EloquentOrganisation::factory()->create();
            },
            'is_default' => false,
            'is_queue' => false,
        ];
    }
}
