<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Organisation;

use App\Http\Requests\Api\ApiRequest;
use App\Models\Organisation;
use App\Services\AuthenticationService;
use Illuminate\Validation\Rule;
use MinVWS\DBCO\Enum\Models\BCOPhase;

/**
 * @property Organisation $organisation
 */
final class CurrentOrganisationUpdateRequest extends ApiRequest
{
    public const FIELD_BCO_PHASE = 'bcoPhase';
    public const FIELD_IS_AVAILABLE_FOR_OUTSOURCING = 'isAvailableForOutsourcing';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(AuthenticationService $authService): array
    {
        $organisation = $authService->getSelectedOrganisation();

        $rules = [
            self::FIELD_BCO_PHASE => ['nullable', 'string', Rule::in(BCOPhase::allValues())],
        ];

        if ($organisation !== null && $organisation->has_outsource_toggle) {
            $rules[self::FIELD_IS_AVAILABLE_FOR_OUTSOURCING] = 'boolean|nullable';
        }

        return $rules;
    }
}
