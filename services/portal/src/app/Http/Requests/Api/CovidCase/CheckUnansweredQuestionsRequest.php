<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CovidCase;

use App\Http\Requests\Api\ApiRequest;

final class CheckUnansweredQuestionsRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'version' => [
                'nullable',
                'string',
            ],
        ];
    }

    public function getVersion(): ?string
    {
        return $this->getStringOrNull('version');
    }
}
