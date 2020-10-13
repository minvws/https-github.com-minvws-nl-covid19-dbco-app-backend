<?php
namespace App\Application\Responses;

use App\Application\DTO\Questionnaire;
use App\Application\Models\QuestionnaireList;
use DBCO\Application\Responses\Response;
use JsonSerializable;

/**
 * Questionnaire list response.
 */
class QuestionnaireListResponse extends Response implements JsonSerializable
{
    /**
     * @var QuestionnaireList
     */
    private QuestionnaireList $questionnaireList;

    /**
     * Constructor.
     *
     * @param QuestionnaireList $questionnaireList
     */
    public function __construct(QuestionnaireList $questionnaireList)
    {
        $this->questionnaireList = $questionnaireList;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return ['questionnaires' => array_map(fn ($q) => new Questionnaire($q), $this->questionnaireList->questionnaires)];
    }
}
