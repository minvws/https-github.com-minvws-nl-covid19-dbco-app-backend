<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentQuestion;
use App\Models\Eloquent\EloquentQuestionnaire;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentQuestionFactory extends Factory
{
    protected $model = EloquentQuestion::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'questionnaire_uuid' => static function () {
                return EloquentQuestionnaire::factory()->create();
            },
            'group_name' => $this->faker->word(),
            'question_type' => $this->faker->word(),
            'label' => $this->faker->word(),
            'relevant_for_categories' => $this->faker->word(),
        ];
    }
}
