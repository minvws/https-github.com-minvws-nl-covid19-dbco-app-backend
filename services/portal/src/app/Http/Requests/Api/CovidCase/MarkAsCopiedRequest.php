<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CovidCase;

use App\Http\Requests\Api\ApiRequest;

final class MarkAsCopiedRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'caseId' => [
                'nullable',
                'string',
            ],
            'taskId' => [
                'nullable',
                'string',
            ],
            'fieldName' => [
                'required',
                'string',
            ],
        ];
    }

    public function getCaseId(): ?string
    {
        return $this->getStringOrNull('caseId');
    }

    public function getFieldName(): string
    {
        return $this->getString('fieldName');
    }

    public function getTaskId(): ?string
    {
        return $this->getStringOrNull('taskId');
    }
}
