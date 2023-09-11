<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentQuestionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentQuestionnaireFactory extends Factory
{
    protected $model = EloquentQuestionnaire::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'name' => $this->faker->sentence(),
            'task_type' => $this->faker->word(),
            'version' => $this->faker->randomNumber(),
        ];
    }
}
