<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentQuestionnaire;
use App\Models\Questionnaire;

class DbQuestionnaireRepository implements QuestionnaireRepository
{
    public function getQuestionnaire(string $questionnaireUuid): ?Questionnaire
    {
        /** @var ?EloquentQuestionnaire $dbQuestionnaire */
        $dbQuestionnaire = EloquentQuestionnaire::query()
            ->where('uuid', $questionnaireUuid)
            ->first();

        if ($dbQuestionnaire !== null) {
            return $this->questionnaireFromEloquentModel($dbQuestionnaire);
        }

        return null;
    }

    public function getLatestQuestionnaire(string $taskType): ?Questionnaire
    {
        /** @var ?EloquentQuestionnaire $dbQuestionnaire */
        $dbQuestionnaire = EloquentQuestionnaire::query()
            ->where('task_type', $taskType)
            ->orderBy('version', 'desc')
            ->first();

        if ($dbQuestionnaire !== null) {
            return $this->questionnaireFromEloquentModel($dbQuestionnaire);
        }

        return null;
    }

    private function questionnaireFromEloquentModel(EloquentQuestionnaire $dbQuestionnaire): Questionnaire
    {
        $questionnaire = new Questionnaire();
        $questionnaire->taskType = $dbQuestionnaire->task_type;
        $questionnaire->uuid = $dbQuestionnaire->uuid;
        $questionnaire->name = $dbQuestionnaire->name;
        $questionnaire->version = (string) $dbQuestionnaire->version;
        return $questionnaire;
    }
}
