<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Questionnaire;

interface QuestionnaireRepository
{
    public function getQuestionnaire(string $questionnaireUuid): ?Questionnaire;

    public function getLatestQuestionnaire(string $taskType): ?Questionnaire;
}
