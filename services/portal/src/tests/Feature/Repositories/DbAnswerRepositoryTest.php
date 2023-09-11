<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\Eloquent\EloquentAnswer;
use App\Models\SimpleAnswer;
use App\Repositories\DbAnswerRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Feature\FeatureTestCase;

class DbAnswerRepositoryTest extends FeatureTestCase
{
    public function testUpdateAnswer(): void
    {
        $uuid = $this->faker->uuid();

        EloquentAnswer::factory()->create(['uuid' => $uuid]);
        $task = $this->createTask();
        $question = $this->createQuestion();

        $answer = new SimpleAnswer();
        $answer->uuid = $uuid;
        $answer->taskUuid = $task->uuid;
        $answer->questionUuid = $question->uuid;
        $answer->value = $this->faker->word();

        $dbAnswerRepository = $this->app->get(DbAnswerRepository::class);

        $result = $dbAnswerRepository->updateAnswer($answer);

        $this->assertEquals($uuid, $result->uuid);
    }

    public function testUpdateAnswerNonExisting(): void
    {
        $answer = new SimpleAnswer();
        $answer->uuid = $this->faker->uuid();

        $dbAnswerRepository = $this->app->get(DbAnswerRepository::class);

        $this->expectException(ModelNotFoundException::class);
        $dbAnswerRepository->updateAnswer($answer);
    }
}
