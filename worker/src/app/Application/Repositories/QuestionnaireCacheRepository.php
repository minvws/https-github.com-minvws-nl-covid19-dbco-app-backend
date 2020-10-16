<?php
namespace App\Application\Repositories;

use App\Application\Models\QuestionnaireList;
use Exception;

/**
 * Store the questionnaire list in the cache.
 *
 * @package App\Application\Repositories
 */
interface QuestionnaireCacheRepository
{
    /**
     * Store the questionnaire list in the cache.
     *
     * @param QuestionnaireList $questionnaires
     *
     * @throws Exception
     */
    public function putQuestionnaires(QuestionnaireList $questionnaires): void;
}

