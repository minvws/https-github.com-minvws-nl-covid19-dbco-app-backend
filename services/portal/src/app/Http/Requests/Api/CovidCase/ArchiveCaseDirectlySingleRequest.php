<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CovidCase;

use App\Http\Requests\Api\ApiRequest;

final class ArchiveCaseDirectlySingleRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note' => [
                'required',
                'string',
            ],
            'sendOsirisNotification' => [
                'boolean',
            ],
        ];
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
