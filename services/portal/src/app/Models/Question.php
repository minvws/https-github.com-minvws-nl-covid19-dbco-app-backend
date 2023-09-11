<?php

declare(strict_types=1);

namespace App\Models;

/**
 * @deprecated use \App\Models\Eloquent\EloquentQuestion, see DBCO-3004
 */
class Question
{
    public const CONTACT_DETAILS = 'contactdetails';
    public const BIRTH_DATE = 'birthdate';

    public string $uuid;

    public string $group;

    public string $questionType;

    public string $label;

    public ?string $header;

    public ?string $description;

    public array $relevantForCategories;

    public ?array $answerOptions; // only used for questionType = multiplechoice

    public function getAnswerValidationRules(): array
    {
        return [];
    }
}
