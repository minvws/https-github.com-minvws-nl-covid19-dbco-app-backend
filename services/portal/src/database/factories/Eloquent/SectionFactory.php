<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use Illuminate\Database\Eloquent\Factories\Factory;

class SectionFactory extends Factory
{
    public $model = Section::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'label' => $this->faker->name(),
            'place_uuid' => static function () {
                return Place::factory()->create();
            },
        ];
    }
}
