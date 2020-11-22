<?php

declare(strict_types=1);

namespace DBCO\HealthAuthorityAPI\Application\DTO;

use DBCO\HealthAuthorityAPI\Application\Models\MultipleChoiceQuestion;
use DBCO\HealthAuthorityAPI\Application\Models\Question as QuestionModel;
use JsonSerializable;

/**
 * Question DTO.
 *
 * @package DBCO\HealthAuthorityAPI\Application\DTO
 */
class Question implements JsonSerializable
{
    /**
     * @var QuestionModel $question
     */
    private QuestionModel $question;

    /**
     * Constructor.
     *
     * @param QuestionModel $question
     */
    public function __construct(QuestionModel $question)
    {
        $this->question = $question;
    }

    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        $result = [
            'uuid' => $this->question->uuid,
            'group' => $this->question->group,
            'questionType' => $this->question->questionType,
            'label' => $this->question->label,
            'description' => $this->question->description,
            'relevantForCategories' => array_map(fn ($c) => ['category' => $c], $this->question->relevantForCategories)
        ];

        if ($this->question instanceof MultipleChoiceQuestion) {
            $result['answerOptions'] = array_map(fn ($o) => new AnswerOption($o), $this->question->answerOptions);
        }

        return $result;
    }
}
