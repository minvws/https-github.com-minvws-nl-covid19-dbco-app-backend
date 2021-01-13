<?php
namespace DBCO\HealthAuthorityAPI\Application\Models;

/**
 * Questionnaire result.
 */
class QuestionnaireResult
{
    /**
     * @var string
     */
    public string $questionnaireUuid;

    /**
     * @var Answer[]
     */
    public array $answers = [];
}
