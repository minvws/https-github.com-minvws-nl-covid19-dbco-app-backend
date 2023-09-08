<?php

declare(strict_types=1);

namespace App\Models\ExpertQuestion;

use App\Models\CovidCase\Contracts\Validatable;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;

class ExpertQuestion implements Validatable
{
    public static function validationRules(array $data): array
    {
        $rules = [];

        $rules[self::SEVERITY_LEVEL_FATAL] = [
            'type' => ['required', Rule::in(ExpertQuestionType::allValues())],
            'subject' => ['required', 'string', 'max:250'],
            'phone' => ['string', 'phone:INTERNATIONAL,NL', 'nullable'],
            'question' => ['required', 'string', 'max:5000'],
        ];

        return $rules;
    }
}
