<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\CaseLabel;
use Illuminate\Database\Eloquent\Factories\Factory;

class CaseLabelFactory extends Factory
{
    protected $model = CaseLabel::class;

    public function definition(): array
    {
        return [
            'code' => $this->faker->unique()->word(),
            'label' => $this->faker->word(),
        ];
    }
}
