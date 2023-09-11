<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Place;

use App\Http\Requests\Api\ApiRequest;
use App\Rules\PlaceBatchPermissionRule;

class VerifyMultipleRequest extends ApiRequest
{
    public function rules(): array
    {
        $rules = [];
        $rules['placeUuids'] = [
            'required',
            'array',
            new PlaceBatchPermissionRule('verify', $this->user()),
        ];

        return $rules;
    }

    /**
     * @return array<string>
     */
    public function getPlaceUuids(): array
    {
        return $this->getArray('placeUuids');
    }
}
