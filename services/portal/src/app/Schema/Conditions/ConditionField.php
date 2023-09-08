<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use Stringable;

/**
 * Represents a field in a condition.
 *
 * Can be used to create simple comparisons.
 */
class ConditionField implements Stringable
{
    private string $field;

    public function __construct(string $field)
    {
        $this->field = $field;
    }

    /**
     * Returns the field name.
     */
    public function getField(): string
    {
        return $this->field;
    }

    /**
     * Creates a === comparison.
     */
    public function identicalTo(mixed $value): Condition
    {
        return new IdenticalToCondition($this->field, $value);
    }

    /**
     * Creates a negated === comparison.
     */
    public function notIdenticalTo(mixed $value): Condition
    {
        return $this->identicalTo($value)->negate();
    }

    /**
     * Creates a == comparison.
     */
    public function equalTo(mixed $value): Condition
    {
        return new EqualToCondition($this->field, $value);
    }

    /**
     * Creates a negated == comparison.
     */
    public function notEqualTo(mixed $value): Condition
    {
        return $this->equalTo($value)->negate();
    }

    /**
     * Creates a < comparison.
     */
    public function lessThan(mixed $value): Condition
    {
        return new LessThanCondition($this->field, $value);
    }

    /**
     * Creates a <= comparison.
     */
    public function lessThanOrEqualTo(mixed $value): Condition
    {
        return new LessThanOrEqualToCondition($this->field, $value);
    }

    /**
     * Creates a > comparison.
     */
    public function greaterThan(mixed $value): Condition
    {
        return new GreaterThanCondition($this->field, $value);
    }

    /**
     * Creates a >= comparison.
     */
    public function greaterThanOrEqualTo(mixed $value): Condition
    {
        return new GreaterThanOrEqualToCondition($this->field, $value);
    }

    /**
     * Checks if the field value is in the given list of values.
     *
     * @param array $values
     */
    public function in(array $values): Condition
    {
        return new InCondition($this->field, $values);
    }

    /**
     * Checks if an array field contains a certain value.
     */
    public function contains(mixed $value): Condition
    {
        return new ContainsCondition($this->field, $value);
    }

    /**
     * Returns the field name.
     */
    public function __toString(): string
    {
        return $this->field;
    }
}
