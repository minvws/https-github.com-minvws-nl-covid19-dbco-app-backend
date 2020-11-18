<?php
declare(strict_types=1);

namespace  DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireResult as QuestionnaireResultModel;
use stdClass;

/**
 * QuestionnaireResult DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class QuestionnaireResult
{
    /**
     * @var QuestionnaireResultModel $questionnaireResult
     */
    private QuestionnaireResultModel $questionnaireResult;

    /**
     * Constructor.
     *
     * @param QuestionnaireResultModel $questionnaireResult
     */
    public function __construct(QuestionnaireResultModel $questionnaireResult)
    {
        $this->questionnaireResult = $questionnaireResult;
    }

    /**
     * Unserialize JSON data structure.
     *
     * @param stdClass $data
     *
     * @return QuestionnaireResultModel
     */
    public static function jsonUnserialize(stdClass $data): QuestionnaireResultModel
    {
        $questionnaireResult = new QuestionnaireResultModel();
        $questionnaireResult->questionnaireUuid = $data->questionnaireUuid;

        $answers = $data->answers ?? [];
        $questionnaireResult->answers = array_map(fn ($a) => Answer::jsonUnserialize($a), $answers);

        return $questionnaireResult;
    }
}
