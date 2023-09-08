<?php

declare(strict_types=1);

namespace App\Repositories;

use Illuminate\Support\Collection;

interface QuestionRepository
{
    public function getQuestions(string $questionnaireUuid): Collection;
}
