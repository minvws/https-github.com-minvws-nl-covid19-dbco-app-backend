<?php

declare(strict_types=1);

namespace App\Rules;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Validation\ImplicitRule;

use function is_string;
use function trans;

class IsBeforeOrEqualRule implements ImplicitRule
{
    public function __construct(private readonly CarbonImmutable $dateBefore, private readonly string $message)
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
        $dateBeforeInstance = CarbonImmutable::instance($this->dateBefore);

        return CarbonImmutable::parse($value)->isBefore($dateBeforeInstance) || CarbonImmutable::parse($value)->isSameDay(
            $dateBeforeInstance,
        );
    }

    public function message(): string
    {
        $result = trans('validation.' . $this->message);
        return is_string($result) ? $result : $this->message;
    }
}
