<?php
namespace App\Application\Repositories;

use App\Application\Models\QuestionnaireList;

/**
 * Used for retrieving questionnaires.
 *
 * @package App\Application\Repositories
 */
interface QuestionnaireRepository
{
    /**
     * Returns the questionnaire list.
     *
     * @return QuestionnaireList
     */
    public function getQuestionnaires(): QuestionnaireList;
}
