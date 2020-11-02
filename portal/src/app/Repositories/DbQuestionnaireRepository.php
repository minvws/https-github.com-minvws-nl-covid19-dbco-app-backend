<?php

namespace App\Repositories;

use App\Models\Eloquent\EloquentQuestionnaire;

class DbQuestionnaireRepository implements QuestionnaireRepository
{
    /**
     * @param string $questionnaireUuid
     * @return Questionnaire
     */
    public function getQuestionnaire(string $questionnaireUuid): Questionnaire
    {
        // TODO
    }
}
