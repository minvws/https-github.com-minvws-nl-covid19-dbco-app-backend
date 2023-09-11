<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseList;

use App\Http\Requests\Api\ApiRequest;
use App\Scopes\CaseListAuthScope;
use Illuminate\Validation\Rule;

class CreateRequest extends ApiRequest
{
    public function rules(CaseListAuthScope $caseListAuthScope): array
    {
        return [
            'name' => [
                'required',
                'string',
                'min:1',
                'max:100',
                $caseListAuthScope->applyToUniqueRule(
                    Rule::unique('case_list', 'name'),
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
