<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Answer;
use App\Models\ContactDetailsAnswer;
use App\Models\Eloquent\EloquentAnswer;
use Illuminate\Support\Collection;

interface AnswerRepository
{
    public function getAllAnswersByTask(string $taskUuid): Collection;

    public function getContactDetailsAnswerByTask(string $taskUuid): ?ContactDetailsAnswer;

    public function createAnswer(Answer $answer): EloquentAnswer;

    public function updateAnswer(Answer $answer): EloquentAnswer;
}
