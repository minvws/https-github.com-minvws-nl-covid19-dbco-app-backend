<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Place\Sections;

use App\Http\Requests\Api\ApiRequest;

class MergeRequest extends ApiRequest
{
    public const MERGE_SECTIONS_LABEL = 'merge_sections';

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            self::MERGE_SECTIONS_LABEL => [
                'required',
                'array',
            ],
            self::MERGE_SECTIONS_LABEL . '.*' => [
                "string",
                "exists:section,uuid",
            ],
        ];
    }
}
