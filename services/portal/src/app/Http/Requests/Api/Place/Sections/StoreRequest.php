<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Place\Sections;

use App\Http\Requests\Api\ApiRequest;

class StoreRequest extends ApiRequest
{
    private const SECTIONS_LABEL = 'sections';

    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            'context_uuid' => 'nullable|uuid',
            self::SECTIONS_LABEL => 'required|array',
            self::SECTIONS_LABEL . '.*' => 'required|array',
            self::SECTIONS_LABEL . '.*.uuid' => 'sometimes|string|exists:section,uuid',
            self::SECTIONS_LABEL . '.*.label' => 'required|string',
        ];
    }

    /**
     * @return array{array{uuid: string, label: string}}
     */
    public function getSections(): array
    {
        /** @var array{array{uuid: string, label: string}} $sections */
        $sections = $this->getArray(self::SECTIONS_LABEL);

        return $sections;
    }
}
