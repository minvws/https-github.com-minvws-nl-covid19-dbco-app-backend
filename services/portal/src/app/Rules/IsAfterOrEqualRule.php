<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;

use function is_string;
use function trans;

class IsAfterOrEqualRule implements ImplicitRule
{
    public function __construct(private readonly CarbonImmutable $dateAfter, private readonly string $message)
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
        $dateAfterInstance = CarbonImmutable::instance($this->dateAfter);

        return CarbonImmutable::parse($value)->isAfter($dateAfterInstance) || CarbonImmutable::parse($value)->isSameDay($dateAfterInstance);
    }

    public function message(): string
    {
        $result = trans('validation.' . $this->message);
        return is_string($result) ? $result : $this->message;
    }
}
