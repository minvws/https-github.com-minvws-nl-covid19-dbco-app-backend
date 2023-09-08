<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use Webmozart\Assert\Assert;

final class BcoPhaseMultiUpdateRequest extends ApiRequest
{
    public const FIELD_BCO_PHASE = 'bco_phase';
    public const FIELD_CASES = 'cases';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            self::FIELD_BCO_PHASE => [Rule::in(BCOPhase::allValues())],
            self::FIELD_CASES => ['required', 'array'],
        ];
    }

    public function getBcoPhase(): BCOPhase
    {
        $bcoPhase = $this->get(self::FIELD_BCO_PHASE);
        Assert::string($bcoPhase);

        return BCOPhase::from($bcoPhase);
    }

    public function getCases(): array
    {
        return $this->get(self::FIELD_CASES);
    }
}
