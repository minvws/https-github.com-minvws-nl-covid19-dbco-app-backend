<?php
namespace DBCO\HealthAuthorityAPI\Application\Responses;

use DBCO\HealthAuthorityAPI\Application\DTO\Questionnaire;
use DBCO\HealthAuthorityAPI\Application\Models\QuestionnaireList;
use DBCO\Shared\Application\Responses\Response;
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
