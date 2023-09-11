<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;

/**
 * Combines 2 conditions using an OR.
 */
class OrCondition extends CombinedCondition
{
    protected function combine(bool $result1, bool $result2): bool
    {
        return $result1 || $result2;
    }

    protected function getOperator(): string
    {
        return Documentation::get('operator', 'or') ?? 'or';
    }

    protected function isFinished(bool $result): bool
    {
        return $result;
    }
}
