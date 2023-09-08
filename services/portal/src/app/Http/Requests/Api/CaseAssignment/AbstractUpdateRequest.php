<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseAssignment;

use App\Http\Requests\Api\ApiRequest;

/**
 * Base class for the case assignment update requests.
 *
 * Contains shared validation rules.
 */
abstract class AbstractUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'assignedOrganisationUuid' => [
                'nullable',
                'uuid',
                'exists:organisation,uuid',
            ],
            'assignedCaseListUuid' => [
                'nullable',
                'uuid',
                'exists:case_list,uuid',
            ],
            'assignedUserUuid' => [
                'nullable',
                'uuid',
                'exists:bcouser,uuid',
            ],
        ];
    }

    public function assignment(): array
    {
        return $this->only(['assignedOrganisationUuid', 'assignedCaseListUuid', 'assignedUserUuid']);
    }
}
