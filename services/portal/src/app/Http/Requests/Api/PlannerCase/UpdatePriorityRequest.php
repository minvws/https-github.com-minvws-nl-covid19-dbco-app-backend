<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\PlannerCase;

use App\Http\Requests\Api\ApiRequest;
use App\Services\CasePriorityService;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\Priority;

use function is_int;
use function sprintf;

class UpdatePriorityRequest extends ApiRequest
{
    private const FIELD_PRIORITY = 'priority';
    private const FIELD_CASES = 'cases';

    public function rules(CasePriorityService $casePriorityService): array
    {
        return [
            self::FIELD_CASES => [
                'required',
                'array',
            ],
            sprintf('%s.*', self::FIELD_CASES) => [
                'bail',
                'uuid',
                static function ($attribute, $value, $fail) use ($casePriorityService): void {
                    if (!$casePriorityService->isPriorityEditAllowed($value)) {
                        $fail('Geen toegang tot case');
                    }
                },
            ],
            self::FIELD_PRIORITY => [
                Rule::in(Priority::allValues()),
            ],
        ];
    }

    public function getCaseUuids(): array
    {
        return $this->get(self::FIELD_CASES);
    }

    public function getPriority(): Priority
    {
        $value = $this->get(self::FIELD_PRIORITY);

        if (!is_int($value)) {
            throw new InvalidArgumentException('invalid value for priority');
        }

        return Priority::from($value);
    }
}
