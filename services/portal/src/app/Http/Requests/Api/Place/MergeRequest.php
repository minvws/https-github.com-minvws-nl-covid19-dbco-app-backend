<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Place;

use App\Http\Requests\Api\ApiRequest;

class MergeRequest extends ApiRequest
{
    public const MERGE_PLACES = 'merge_places';

    public function validationData(): array
    {
        return [
            self::MERGE_PLACES => $this->get(self::MERGE_PLACES),
        ];
    }

    public function rules(): array
    {
        return [
            self::MERGE_PLACES => [
                'required',
                'array',
            ],
        ];
    }
}
