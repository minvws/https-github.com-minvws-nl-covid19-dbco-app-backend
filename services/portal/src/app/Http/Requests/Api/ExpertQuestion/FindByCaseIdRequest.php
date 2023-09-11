<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\ExpertQuestion;

use App\Http\Requests\Api\ApiRequest;
use MinVWS\DBCO\Enum\Models\ExpertQuestionType;

use function is_array;
use function is_string;
use function reset;

class FindByCaseIdRequest extends ApiRequest
{
    public const FIELD_CASE_ID = 'case_id';
    public const FIELD_EXPERT_QUESTION_TYPE = 'expert_question_type';

    public function rules(): array
    {
        return [
            self::FIELD_CASE_ID => [
                'required',
                'string',
            ],
            self::FIELD_EXPERT_QUESTION_TYPE => [
                'required',
                'string',
            ],
        ];
    }

    public function getPostCaseId(): ?string
    {
        $value = $this->post(self::FIELD_CASE_ID);

        if (is_array($value)) {
            return reset($value) ?: null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        return $value;
    }

    public function getExpertQuestionType(): ?ExpertQuestionType
    {
        $value = $this->post(self::FIELD_EXPERT_QUESTION_TYPE);

        if (!is_string($value)) {
            return null;
        }

        return ExpertQuestionType::tryFrom($value);
    }
}
