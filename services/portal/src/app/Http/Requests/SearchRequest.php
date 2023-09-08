<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Http\Requests\Api\ApiRequest;

use function is_string;

class SearchRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'caseUuid' => 'nullable|string',
            'taskUuid' => 'nullable|string',
            'email' => 'nullable|string',
            'dateOfBirth' => 'nullable|date_format:Y-m-d|after_or_equal:1901-01-01',
            'identifier' => 'nullable|min:6|max:16',
            'lastname' => 'nullable|string',
            'phone' => 'nullable|string|phone:INTERNATIONAL,NL',
        ];
    }

    public function getIdentifier(): ?string
    {
        $identifier = $this->get('identifier');

        if (!is_string($identifier) || $identifier === '') {
            return null;
        }

        return $identifier;
    }
}
