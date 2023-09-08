<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Admin\RiskProfile;

use App\Http\Requests\Api\ApiRequest;
use Illuminate\Contracts\Validation\ValidationRule;

class UpdateRiskProfileRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, (ValidationRule|array|string)>
     */
    public function rules(): array
    {
        return [
            'policyGuidelineUuid' => 'required|string|exists:policy_guideline,uuid',
        ];
    }
}
