<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function is_null;
use function is_string;
use function trans;

class DeceasedAndUnderlyingSufferingUnder70Rule implements ImplicitRule
{
    public function __construct(
        private readonly CarbonImmutable $caseCreatedAt,
        private readonly ?CarbonImmutable $dateOfBirth,
        private readonly ?YesNoUnknown $hasUnderlyingSuffering,
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

        return $this->validate($value);
    }

    public function validate(string $value): bool
    {
        return !$this->isDeceased($value)
            || $this->hasUnderLyingSufferingBeenAnswered()
            || $this->isIndexOlderThan70();
    }

    public function isDeceased(string $value): bool
    {
        return YesNoUnknown::from($value) === YesNoUnknown::yes();
    }

    public function hasUnderLyingSufferingBeenAnswered(): bool
    {
        return !is_null($this->hasUnderlyingSuffering);
    }

    public function isIndexOlderThan70(): bool
    {
        if (is_null($this->dateOfBirth)) {
            //If no age is known (should not occur) we assume under 70
            return false;
        }

        return $this->caseCreatedAt->isAfter($this->dateOfBirth->add('70 years'));
    }

    public function message(): string
    {
        $message = 'Underlying suffering should be asked for.';
        $result = trans('validation.' . $message);
        return is_string($result) ? $result : $message;
    }
}
