<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Place;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\ContextCategory;

class UpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'label' => [
                'required',
                'string',
            ],
            'id' => [ // this is the place.location_id
                'nullable',
                'string',
            ],
            'organisationUuid' => [
                'nullable',
                'exists:organisation,uuid',
                'string',
            ],
            'category' => [
                'required',
                Rule::in(ContextCategory::allValues()),
                'string',
            ],
            'address.street' => [
                'nullable',
                'string',
            ],
            'address.postalCode' => [
                'nullable',
                'postal_code:NL',
            ],
            'address.houseNumber' => [
                'nullable',
                'string',
            ],
            'address.houseNumberSuffix' => [
                'nullable',
                'string',
            ],
            'address.town' => [
                'nullable',
                'string',
            ],
            'ggd.code' => [
                'nullable',
                'string',
            ],
            'ggd.municipality' => [
                'nullable',
                'string',
            ],
            'isVerified' => [
                'nullable',
                'bool',
            ],

            // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
            // TODO: Should be reverted with ticket BOOST-46 (All situation_numbers fields)
            // https://ggdcontact.atlassian.net/browse/BOOST-46
            'situationNumbers' => [
                'array',
            ],
            'situationNumbers.*.uuid' => [
                'string',
                'nullable',
            ],
            'situationNumbers.*.name' => [
                'string',
                'nullable',
            ],
            'situationNumbers.*.value' => [
                'string',
                'nullable',
            ],
        ];
    }
}
