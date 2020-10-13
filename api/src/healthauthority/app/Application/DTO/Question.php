<?php

declare(strict_types=1);

namespace App\Application\DTO;

use App\Application\Models\MultipleChoiceQuestion;
use App\Application\Models\Question as QuestionModel;
use JsonSerializable;

/**
 * Question DTO.
 *
 * @package App\Application\Actions
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
            'id' => $this->question->id,
            'group' => $this->question->group,
            'questionType' => $this->question->questionType,
            'label' => $this->question->label,
            'description' => $this->question->description,
            'relevantForCategories' => $this->question->relevantForCategories
        ];

        if ($this->question instanceof MultipleChoiceQuestion) {
            $result['answerOptions'] = array_map(fn ($o) => new AnswerOption($o), $this->question->answerOptions);
        }

        return $result;
    }
}
