<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseAssignment;

/**
 * @property-read array<string> $cases
 */
class UpdateMultiRequest extends AbstractUpdateRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['cases'] = ['required', 'array'];
        $rules['cases.*'] = ['required', 'uuid'];

        return $rules;
    }
}
