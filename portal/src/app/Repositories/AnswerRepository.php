<?php

namespace App\Repositories;

use App\Models\Answer;
use Illuminate\Support\Collection;

interface AnswerRepository
{
    public function getAllAnswersByCase(string $caseUuid): Collection;

    public function getAllAnswersByTask(string $taskUuid): Collection;

    public function updateAnswer(Answer $answer);
}
