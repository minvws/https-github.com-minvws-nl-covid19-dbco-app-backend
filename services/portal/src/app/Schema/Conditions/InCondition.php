<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Documentation\Documentation;

use function implode;
use function in_array;

/**
 * Condition that checks if the value of a field is within a certain list of values.
 */
class InCondition extends Condition
{
    private string $field;
    private array $values;

    public function __construct(string $field, array $values)
    {
        $this->field = $field;
        $this->values = $values;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        return [$this->field];
    }

    /**
     * @inheritDoc
     */
    public function evaluate(array $data): bool
    {
        $value = $data[$this->field] ?? null;
        return in_array($value, $this->values, true);
    }

    protected function buildString(int $level): string
    {
        return $this->field . ' ' . (Documentation::get('operator', 'in') ?? 'in') . ' (' . implode(', ', $this->values) . ')';
    }
}
