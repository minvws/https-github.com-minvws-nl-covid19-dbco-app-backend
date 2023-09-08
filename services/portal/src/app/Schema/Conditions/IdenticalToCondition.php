<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;

/**
 * Performs a === comparison.
 */
class IdenticalToCondition extends ComparisonCondition
{
    protected function compare(mixed $value1, mixed $value2): bool
    {
        return $value1 === $value2;
    }

    protected function getOperator(): string
    {
        return Documentation::get('operator', 'identicalTo') ?? '===';
    }
}
