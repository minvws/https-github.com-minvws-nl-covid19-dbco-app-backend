<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use function array_map;
use function array_merge;
use function array_reduce;
use function array_unique;
use function implode;

/**
 * Combines 2 or more conditions.
 */
abstract class CombinedCondition extends Condition
{
    private array $conditions;

    public function __construct(Condition $condition1, Condition $condition2, Condition ...$conditions)
    {
        $this->conditions = [$condition1, $condition2, ...$conditions];
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return array_unique(
            array_reduce(
                $this->conditions,
                static fn (array $r, Condition $c) => array_merge($r, $c->getFields()),
                [],
            ),
        );
    }

    /**
     * Combines the results of 2 conditions.
     */
    abstract protected function combine(bool $result1, bool $result2): bool;

    /**
     * Returns a string representation of the used logical operator.
     */
    abstract protected function getOperator(): string;

    /**
     * Is evaluation finished with the current (intermediate) result?
     */
    protected function isFinished(bool $result): bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    final public function evaluate(array $data): bool
    {
        $result = null;
        foreach ($this->conditions as $condition) {
            $value = $condition->evaluate($data);

            $result = $result === null ? $value : $this->combine($result, $value);

            if ($this->isFinished($result)) {
                break;
            }
        }

        return $result ?? false;
    }

    protected function buildString(int $level): string
    {
        $result = implode(
            ' ' . $this->getOperator() . ' ',
            array_map(static fn (Condition $c) => $c->buildString($level + 1), $this->conditions),
        );

        if ($level > 0) {
            $result = '(' . $result . ')';
        }

        return $result;
    }
}
