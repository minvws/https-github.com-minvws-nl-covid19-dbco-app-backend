<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function is_string;
use function trans;

class IsUnder18InICURule implements ImplicitRule
{
    public function __construct(private readonly CarbonImmutable $dateOfBirth)
    {
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
        if (!$this->isInICU($value)) {
            return true;
        }
        $dateOf18thBirthday = $this->dateOfBirth->add('18 years');
        return CarbonImmutable::parse('today')->isAfter(CarbonImmutable::instance($dateOf18thBirthday));
    }

    public function message(): string
    {
        $message = 'This person is under the age of 18.';
        $result = trans('validation.' . $message);
        return is_string($result) ? $result : $message;
    }

    public function isInICU(string $value): bool
    {
        return $value === YesNoUnknown::yes()->value;
    }
}
