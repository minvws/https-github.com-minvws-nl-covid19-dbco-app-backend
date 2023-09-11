<?php

declare(strict_types=1);

namespace App\Http\Requests\Api;

use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\BCOPhase;

final class BcoPhaseUpdateRequest extends ApiRequest
{
    public const FIELD_BCO_PHASE = 'bco_phase';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            self::FIELD_BCO_PHASE => [Rule::in(BCOPhase::allValues())],
        ];
    }

    public function getBcoPhase(): BCOPhase
    {
        /** @phpstan-ignore-next-line */
        return BCOPhase::from($this->get(self::FIELD_BCO_PHASE));
    }
}
