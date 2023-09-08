<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\CaseUpdate;

use App\Http\Requests\Api\ApiRequest;
use Webmozart\Assert\Assert;

class ApplyCaseUpdateRequest extends ApiRequest
{
    public function rules(): array
    {
        return [
            'fieldIds' => ['array'],
            'fieldIds.*' => ['string'],
        ];
    }

    /**
     * @return array<string>
     */
    public function getFieldIds(): array
    {
        $fieldIds = $this->input('fieldIds', []);

        Assert::isArray($fieldIds);
        Assert::allString($fieldIds);

        return $fieldIds;
    }
}
