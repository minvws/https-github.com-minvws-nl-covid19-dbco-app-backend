<?php

namespace App\Repositories;

use Illuminate\Support\Collection;

interface QuestionRepository
{
    public function getQuestions(string $questionnaireUuid): Collection;
}
