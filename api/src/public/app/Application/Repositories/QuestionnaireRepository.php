<?php
namespace DBCO\PublicAPI\Application\Repositories;

use DBCO\PublicAPI\Application\Models\QuestionnaireList;

/**
 * Used for retrieving questionnaires.
 *
 * @package DBCO\PublicAPI\Application\Repositories
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
