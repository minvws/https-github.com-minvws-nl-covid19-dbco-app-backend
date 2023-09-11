<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CovidCase;

use App\Http\Requests\Api\ApiRequest;

final class ArchiveCaseDirectlyMultipleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cases' => [
                'required',
                'array',
            ],
            'cases.*' => [
                'exists:covidcase,uuid',
            ],
            'note' => [
                'required',
                'string',
            ],
            'sendOsirisNotification' => [
                'boolean',
            ],
        ];
    }

    /**
     * @return array<string>
     */
    public function getCases(): array
    {
        return $this->getArray('cases');
    }

    public function getNote(): string
    {
        return $this->getString('note');
    }

    public function getSendOsirisNotification(): bool
    {
        return $this->getBoolean('sendOsirisNotification', true);
    }
}
