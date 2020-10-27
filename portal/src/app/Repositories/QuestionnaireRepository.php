<?php

namespace App\Repositories;

interface QuestionnaireRepository
{
    public function getQuestionnaire(string $questionnaireUuid): Questionnaire;
}
