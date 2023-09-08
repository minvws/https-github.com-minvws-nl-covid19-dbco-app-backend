<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\Fields\Field;
use Closure;
use Stringable;

/**
 * Represents dependency information for a field.
 */
abstract class Condition implements Stringable
{
    /**
     * Returns the fields this condition references.
     *
     * @return array
     */
    abstract public function getFields(): array;

    /**
     * Evaluates the condition.
     *
     * @param array $data Data for the fields this condition references.
     *
     * @return bool Result of condition evaluation.
     */
    abstract public function evaluate(array $data): bool;

    /**
     * Returns a string representation for the condition.
     *
     * @param int $level Build level (0 is root, > 0 is nested).
     */
    abstract protected function buildString(int $level): string;

    /**
     * Returns a string representation for the condition.
     */
    public function __toString(): string
    {
        return $this->buildString(0);
    }

    /**
     * Negates the condition.
     */
    public function negate(): Condition
    {
        return new NotCondition($this);
    }

    /**
     * AND's this condition with one or more conditions.
     */
    public function and(Condition ...$conditions): Condition
    {
        return new AndCondition($this, ...$conditions);
    }

    /**
     * OR's this condition with one or more conditions.
     */
    public function or(Condition ...$conditions): Condition
    {
        return new OrCondition($this, ...$conditions);
    }

    /**
     * Returns a condition field which can be used to create a comparison.
     */
    public static function field(Field|string $field): ConditionField
    {
        return new ConditionField($field instanceof Field ? $field->getName() : $field);
    }

    /**
     * Create a 100% custom condition with custom logic.
     *
     * WARNING:
     * We can't auto-document custom conditions!
     *
     * @param array $fields
     */
    public static function custom(array $fields, Closure $condition): Condition
    {
        return new CustomCondition($fields, $condition);
    }
}
