<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\PolicyGuideline;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\PolicyGuidelineReferenceField;

class UpdatePolicyGuidelineRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, (ValidationRule|array|string)>
     */
    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string'],
            'sourceStartDateReference' => ['sometimes', Rule::in(PolicyGuidelineReferenceField::allValues())],
            'sourceStartDateAddition' => ['sometimes', 'integer'],
            'sourceEndDateReference' => ['sometimes', Rule::in(PolicyGuidelineReferenceField::allValues())],
            'sourceEndDateAddition' => ['sometimes', 'integer'],
            'contagiousStartDateReference' => ['sometimes', Rule::in(PolicyGuidelineReferenceField::allValues())],
            'contagiousStartDateAddition' => ['sometimes', 'integer'],
            'contagiousEndDateReference' => ['sometimes', Rule::in(PolicyGuidelineReferenceField::allValues())],
            'contagiousEndDateAddition' => ['sometimes', 'integer'],
        ];
    }
}
