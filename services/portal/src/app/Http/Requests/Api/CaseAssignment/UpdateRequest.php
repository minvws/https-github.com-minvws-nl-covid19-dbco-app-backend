<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseAssignment;

use App\Models\Eloquent\EloquentCase;

/**
 * @property EloquentCase $case
 */
class UpdateRequest extends AbstractUpdateRequest
{
    public function validationData(): array
    {
        $data = parent::validationData();
        $data['case'] = $this->case->uuid;
        return $data;
    }

    public function rules(): array
    {
        $rules = parent::rules();
        $rules['case'] = ['required', 'uuid'];
        return $rules;
    }
}
