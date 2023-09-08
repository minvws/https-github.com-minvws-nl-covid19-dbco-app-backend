<?php

declare(strict_types=1);

namespace Database\Factories\Eloquent;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Eloquent\ExpertQuestion;
use App\Models\Eloquent\ExpertQuestionAnswer;
use App\Models\Eloquent\Timeline;
use Carbon\CarbonImmutable;
use Database\Factories\Eloquent\Traits\WithFragments;
use Illuminate\Database\Eloquent\Factories\Factory;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;

class ExpertQuestionFactory extends Factory
{
    use WithFragments;

    protected $model = ExpertQuestion::class;

    public function definition(): array
    {
        $createdAt = new CarbonImmutable($this->faker->dateTimeBetween('-3 days'));

        return [
            'uuid' => $this->faker->uuid(),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
            'case_uuid' => static function () {
                return EloquentCase::factory()->create();
            },
            'case_created_at' => $createdAt,
            'user_uuid' => static function () {
                return EloquentUser::factory()->create();
            },
            'assigned_user_uuid' => null,
            'type' => $this->faker->randomElement(ExpertQuestionType::all()),
            'subject' => $this->faker->text(50),
            'phone' => $this->faker->optional()->phoneNumber(),
            'question' => $this->faker->text(255),
        ];
    }

    public function configure(): self
    {
        return $this->afterMaking(static function (ExpertQuestion $expertQuestion): void {
            /** @var Timeline $timeline */
            $timeline = Timeline::make();
            $timeline->case_uuid = $expertQuestion->case_uuid;
            $timeline->timelineable()->associate($expertQuestion);
            $timeline->save();
        });
    }

    public function withAnswer(): self
    {
        return $this->afterMaking(static function (ExpertQuestion $expertQuestion): void {
            ExpertQuestionAnswer::factory()->create([
                'expert_question_uuid' => $expertQuestion->uuid,
            ]);
        });
    }
}
