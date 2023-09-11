<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CovidCase;

use App\Http\Requests\Api\ApiRequest;

class UpdateCaseOrganisationRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'organisation_uuid' => [
                'required',
                'string',
                'exists:organisation,uuid',
            ],
            'note' => 'string',
        ];
    }

    public function getOrganisationUuid(): string
    {
        return $this->getString('organisation_uuid');
    }

    public function getNote(): ?string
    {
        return $this->getStringOrNull('note');
    }
}
