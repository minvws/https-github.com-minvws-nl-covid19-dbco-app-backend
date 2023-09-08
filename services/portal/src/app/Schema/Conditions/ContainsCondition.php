<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;

use function in_array;
use function is_array;

/**
 * Performs a == comparison.
 */
class ContainsCondition extends ComparisonCondition
{
    protected function compare(mixed $value1, mixed $value2): bool
    {
        // phpcs:disable SlevomatCodingStandard.Functions.StrictCall
        return is_array($value1) && in_array($value2, $value1);
    }

    protected function getOperator(): string
    {
        return Documentation::get('operator', 'contains') ?? 'contains';
    }
}
