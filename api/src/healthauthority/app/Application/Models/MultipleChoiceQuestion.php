<?php
namespace App\Application\Models;

/**
 * Multiple choice question.
 */
class MultipleChoiceQuestion extends Question
{
    /**
     * @var string
     */
    public string $questionType = 'multiplechoice';

    /**
     * @var AnswerOption[]
     */
    public array $answerOptions = [];
}
