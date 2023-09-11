<?php

declare(strict_types=1);

namespace App\Rules\UnderlyingSuffering;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;

use function is_null;
use function is_string;
use function trans;

class UnderlyingSufferingUnder70Rule implements ImplicitRule
{
    public function __construct(
        private readonly CarbonImmutable $caseCreatedAt,
        private readonly ?CarbonImmutable $dateOfBirth,
    ) {
    }

    /**
     * @inheritdoc
     */
    public function passes($attribute, $value): bool
    {
        //Invalid value
        if (!empty($value) && !is_string($value)) {
            return false;
        }

        if (!empty($value)) {
            return true;
        }

        return $this->validate();
    }

    public function validate(): bool
    {
        return $this->isIndexOlderThan70();
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
