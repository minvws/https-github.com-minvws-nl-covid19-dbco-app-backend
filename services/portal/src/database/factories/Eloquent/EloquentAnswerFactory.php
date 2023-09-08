<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentAnswer;
use App\Models\Eloquent\EloquentQuestion;
use App\Models\Eloquent\EloquentTask;
use Database\Factories\FactoryHelper;
use Illuminate\Database\Eloquent\Factories\Factory;

class EloquentAnswerFactory extends Factory
{
    protected $model = EloquentAnswer::class;

    public function definition(): array
    {
        return [
            'uuid' => $this->faker->uuid(),
            'task_uuid' => static function () {
                return EloquentTask::factory()->create();
            },
            'question_uuid' => static function () {
                return EloquentQuestion::factory()->create();
            },
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(static function (EloquentAnswer $eloquentAnswer): void {
            FactoryHelper::sealValuesShort($eloquentAnswer, [
                'spv_value',
                'ctd_firstname',
                'ctd_lastname',
                'ctd_email',
                'ctd_phonenumber',
            ]);
        });
    }
}
