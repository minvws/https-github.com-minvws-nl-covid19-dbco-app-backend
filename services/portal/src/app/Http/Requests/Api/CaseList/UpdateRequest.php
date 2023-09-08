<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseList;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\CaseList;
use App\Scopes\CaseListAuthScope;
use Illuminate\Validation\Rule;

/**
 * @property CaseList $caseList
 */
class UpdateRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return !$this->caseList->is_default;
    }

    public function rules(CaseListAuthScope $caseListAuthScope): array
    {
        return [
            'name' => [
                'string',
                $caseListAuthScope->applyToUniqueRule(
                    Rule::unique('case_list', 'name')
                        ->ignore($this->route('caseList'), 'uuid'),
                ),
            ],
            'isQueue' => [
                'boolean',
                static function ($attr, $value, $fail): void {
                    if ($value !== false) {
                        $fail('Eigen queues worden nog niet ondersteund!');
                    }
                },
            ],
        ];
    }
}
