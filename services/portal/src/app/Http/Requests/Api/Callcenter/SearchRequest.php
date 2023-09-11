<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Callcenter;

use App\Http\Requests\Api\ApiRequest;

/**
 * @property string $note
 */
class SearchRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'dateOfBirth' => ['required', 'date_format:d-m-Y', 'after_or_equal:1900-01-01'],
            'lastThreeBsnDigits' => ['required_without:lastname', 'string', 'digits:3', 'nullable'],
            'lastname' => ['required_without:lastThreeBsnDigits', 'string', 'nullable'],
            'postalCode' => ['required_without:phone', 'string', 'postal_code:NL', 'nullable'],
            'houseNumber' => ['required_without:phone', 'nullable'],
            'houseNumberSuffix' => ['nullable', 'string'],
            'phone' => ['required_without:postalCode', 'string', 'phone:INTERNATIONAL,NL', 'nullable'],
        ];
    }
}
