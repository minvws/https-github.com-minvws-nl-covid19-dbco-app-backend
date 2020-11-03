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
     * @param string $language Language.
     *
     * @return QuestionnaireList
     */
    public function getQuestionnaires(string $language): QuestionnaireList;
}
