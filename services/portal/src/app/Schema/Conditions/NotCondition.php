<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;

/**
 * Negates a condition.
 */
class NotCondition extends Condition
{
    private Condition $condition;

    public function __construct(Condition $condition)
    {
        $this->condition = $condition;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return $this->condition->getFields();
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $data): bool
    {
        return !$this->condition->evaluate($data);
    }

    protected function buildString(int $level): string
    {
        return (Documentation::get('operator', 'not') ?? 'not') . ' (' . $this->condition->buildString($level + 1) . ')';
    }
}
