<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use DateTimeInterface;
use Stringable;

use function is_object;
use function method_exists;
use function var_export;

/**
 * Base class for conditions that compare 2 values.
 */
abstract class ComparisonCondition extends Condition
{
    private string $field;
    private mixed $value;

    public function __construct(string $field, mixed $value)
    {
        $this->field = $field;
        $this->value = $value;
    }

    /**
     * @inheritDoc
     */
    public function getFields(): array
    {
        $fields = [$this->field];
        if ($this->value instanceof ConditionField) {
            $fields[] = $this->value->getField();
        }
        return $fields;
    }

    abstract protected function compare(mixed $value1, mixed $value2): bool;

    /**
     * Returns a string representation of the used comparison operator.
     */
    abstract protected function getOperator(): string;

    /**
     * @inheritDoc
     */
    final public function evaluate(array $data): bool
    {
        $value1 = $data[$this->field] ?? null;
        $value2 = $this->value;

        if ($value2 instanceof ConditionField) {
            $value2 = $data[$value2->getField()] ?? null;
        }

        return $this->compare($value1, $value2);
    }

    protected function buildString(int $level): string
    {
        $value = $this->value;
        $isField = $value instanceof ConditionField;

        if ($value instanceof Stringable || (is_object($value) && method_exists($value, '__toString'))) {
            $value = (string) $value;
        } elseif ($value instanceof DateTimeInterface) {
            $value = $value->format('c');
        }

        if (!$isField) {
            $value = var_export($value, true);
        }

        return $this->field . ' ' . $this->getOperator() . ' ' . $value;
    }
}
