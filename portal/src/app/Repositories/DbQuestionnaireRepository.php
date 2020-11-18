<?php

namespace App\Repositories;

use App\Models\Eloquent\EloquentQuestionnaire;
use App\Models\Questionnaire;

class DbQuestionnaireRepository implements QuestionnaireRepository
{
    /**
     * @param string $questionnaireUuid
     * @return Questionnaire
     */
    public function getQuestionnaire(string $questionnaireUuid): ?Questionnaire
    {
        $dbQuestionnaire = EloquentQuestionnaire::where('uuid', $questionnaireUuid)->get()->first();
        if ($dbQuestionnaire) {
            return $this->questionnaireFromEloquentModel($dbQuestionnaire);
        }
        return null;
    }

    public function getLatestQuestionnaire(string $taskType): ?Questionnaire
    {
        $dbQuestionnaire = EloquentQuestionnaire::where('task_type', $taskType)->orderBy('version', 'desc')->take(1)->first();
        if ($dbQuestionnaire) {
            return $this->questionnaireFromEloquentModel($dbQuestionnaire);
        }
        return null;
    }

    public function questionnaireFromEloquentModel(EloquentQuestionnaire $dbQuestionnaire): Questionnaire
    {
        $questionnaire = new Questionnaire();
        $questionnaire->taskType = $dbQuestionnaire->task_type;
        $questionnaire->uuid = $dbQuestionnaire->uuid;
        $questionnaire->name = $dbQuestionnaire->name;
        $questionnaire->version = $dbQuestionnaire->version;
        return $questionnaire;
    }
}
