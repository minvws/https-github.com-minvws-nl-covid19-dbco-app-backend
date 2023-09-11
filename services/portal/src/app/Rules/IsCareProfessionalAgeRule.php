<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;

use function is_string;
use function trans;

/**
 * Validates if the dateOfBirth ($value) of Care Professional is before (younger) or
 * after (older) than the given age ($this->ageInYears).
 */
class IsCareProfessionalAgeRule implements ImplicitRule
{
    public function __construct(
        private readonly bool $isCareProfessional,
        private readonly int $ageInYears,
        private readonly string $dateCompare,
        private readonly string $message,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function passes($attribute, $value): bool
    {
        if (empty($value)) {
            return true;
        }

        if (!is_string($value)) {
            return false;
        }

        if (!$this->isCareProfessional) {
            return true;
        }

        $dateCompare = $this->dateCompare;
        return (CarbonImmutable::parse($value)->addYears($this->ageInYears))->{$dateCompare}(CarbonImmutable::now());
    }

    public function message(): string
    {
        $result = trans('validation.' . $this->message);
        return is_string($result) ? $result : $this->message;
    }
}
