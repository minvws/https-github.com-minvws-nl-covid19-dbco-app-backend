<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent\Disease;

use App\Models\Disease\Disease;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiseaseFactory extends Factory
{
    protected $model = Disease::class;

    public function definition(): array
    {
        $name = $this->faker->word;
        return [
            'code' => $this->faker->asciify($name),
            'name' => $name,
        ];
    }
}
