<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Case;

use App\Http\Requests\Api\ApiRequest;

class MarkAsCopiedRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'caseId' => [
                'required',
                'string',
            ],
            'fieldName' => [
                'required',
                'string',
            ],
            'taskId' => [
                'string',
                'nullable',
            ],
        ];
    }

    public function getCaseId(): string
    {
        return $this->getString('caseId');
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
