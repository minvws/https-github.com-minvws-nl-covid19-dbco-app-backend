<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\ExpertQuestionAnswer;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Traits\WithFragments;
use Illuminate\Database\Eloquent\Factories\Factory;

class ExpertQuestionAnswerFactory extends Factory
{
    use WithFragments;

    protected $model = ExpertQuestionAnswer::class;

    public function definition(): array
    {
        $createdAt = new CarbonImmutable($this->faker->dateTimeBetween('-1 months'));

        return [
            'uuid' => $this->faker->uuid(),
            'expert_question_uuid' => static function () {
                return ExpertQuestion::factory()->create();
            },
            'case_created_at' => $createdAt,
            'answer' => $this->faker->paragraph(),
            'answered_by' => static function () {
                return EloquentUser::factory()->create();
            },
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ];
    }
}
