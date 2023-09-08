<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;

/**
 * Combines 2 or more conditions using an AND.
 */
class AndCondition extends CombinedCondition
{
    protected function combine(bool $result1, bool $result2): bool
    {
        return $result1 && $result2;
    }

    protected function getOperator(): string
    {
        return Documentation::get('operator', 'and') ?? 'and';
    }

    protected function isFinished(bool $result): bool
    {
        return !$result;
    }
}
