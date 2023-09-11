<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\PlannerCase;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Eloquent\CaseList;
use App\Services\AuthenticationService;

/**
 * @property-read ?CaseList $caseList
 */
class CountRequest extends ApiRequest
{
    public function validationData(): array
    {
        $data = parent::validationData();
        $data['caseListUuid'] = $this->caseList->uuid ?? null;
        return $data;
    }

    public function authorize(AuthenticationService $authenticationService): bool
    {
        if ($this->caseList === null) {
            return true;
        }

        $selectedOrganisation = $authenticationService->getRequiredSelectedOrganisation();
        return $this->caseList->organisation_uuid === $selectedOrganisation->uuid && !$this->caseList->is_queue;
    }

    public function rules(): array
    {
        return [
            'caseListUuid' => [
                'string',
                'nullable',
                'exists:case_list,uuid',
            ],
        ];
    }
}
