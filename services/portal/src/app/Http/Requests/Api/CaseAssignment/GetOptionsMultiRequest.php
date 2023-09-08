<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseAssignment;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\EloquentCase;
use App\Rules\ExistsRule;

class GetOptionsMultiRequest extends ApiRequest
{
    public function rules(): array
    {
        $rules = [];
        $rules['cases'] = [
            'required',
            'array',
            new ExistsRule(EloquentCase::class, 'uuid'),
        ];

        return $rules;
    }

    /**
     * @return array<string>
     */
    public function getCases(): array
    {
        /** @var array<string> $cases */
        $cases = $this->get('cases');

        return $cases;
    }
}
