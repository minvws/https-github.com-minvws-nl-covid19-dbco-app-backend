<?php
namespace DBCO\HealthAuthorityAPI\Application\Repositories;

use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireList;

/**
 * Used for retrieving questionnaires.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Repositories
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
