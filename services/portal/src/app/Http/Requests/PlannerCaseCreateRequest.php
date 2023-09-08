<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Api\ApiRequest;
use App\Rules\CaseLabelPermissionRule;

class PlannerCaseCreateRequest extends ApiRequest
{
    public function rules(CaseLabelPermissionRule $caseLabelPermissionRule): array
    {
        return [
            'caseLabels' => [
                'array',
            ],
            'caseLabels.*' => $caseLabelPermissionRule,
            'notes' => [
                'string',
                'nullable',
                'max:5000',
            ],
        ];
    }

    public function getNotes(): ?string
    {
        return $this->getStringOrNull('notes');
    }
}
