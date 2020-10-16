<?php
namespace App\Application\Repositories;

use App\Application\Models\QuestionnaireList;

interface QuestionnaireGetRepository
{
    /**
     * Retrieve the signed questionnaires for pass through usage.
     *
     * The returned object contains the response body from the source plus headers
     * that contain and are required to verify the body contents signature.
     *
     * @return QuestionnaireList
     *
     * @throws Exception
     */
    public function getQuestionnaires(): QuestionnaireList;
}
