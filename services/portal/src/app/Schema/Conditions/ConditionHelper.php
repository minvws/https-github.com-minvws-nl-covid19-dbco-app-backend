<?php

declare(strict_types=1);

namespace App\Schema\Conditions;

use App\Schema\SchemaObject;
use MJS\TopSort\CircularDependencyException;
use MJS\TopSort\ElementNotFoundException;
use MJS\TopSort\Implementations\StringSort;

use function array_unique;
use function count;
use function explode;
use function spl_object_hash;

/**
 * Helper for sorting fields based on their dependencies and makes sure results for
 * conditions are re-used.
 */
class ConditionHelper
{
    /** @var array<string, Condition|null> */
    private array $conditions = [];

    private StringSort $sort;

    /** @var array<string, bool> */
    private array $result = [];

    public function __construct()
    {
        $this->sort = new StringSort();
        $this->sort->setThrowCircularDependency(true);
    }

    /**
     * Register a field and its optional condition.
     */
    public function add(string $field, ?Condition $condition = null): void
    {
        $this->conditions[$field] = $condition;
        $this->sort->add($field, $condition ? $this->filterTopLevelFields($condition->getFields()) : []);
    }

    /**
     * Filters the top level fields from the field expressions.
     *
     * @param array<string> $fields
     *
     * @return array<string>
     */
    private function filterTopLevelFields(array $fields): array
    {
        if (count($fields) === 0) {
            return $fields;
        }

        $topLevelFields = [];
        foreach ($fields as $field) {
            [$topLevelField] = explode('.', $field);
            $topLevelFields[] = $topLevelField;
        }

        return array_unique($topLevelFields);
    }

    /**
     * Returns a topological sort of the fields.
     *
     * It tells you which fields need to be processed first in order to fulfil all conditions in the correct order.
     *
     * @return array<string>
     *
     * @throws CircularDependencyException If a circular condition has been found
     * @throws ElementNotFoundException If a condition cannot be found
     */
    public function getSortedFields(): array
    {
        return $this->sort->sort();
    }

    /**
     * Extract data of the object for the given field.
     *
     * @param SchemaObject $object Object.
     * @param array $fields Field names.
     *
     * @return array
     */
    private function extractDataForFields(SchemaObject $object, array $fields): array
    {
        $data = [];

        foreach ($fields as $field) {
            $parts = explode('.', $field);

            $value = $object;
            foreach ($parts as $part) {
                $value = $value->$part ?? null;
            }

            $data[$field] = $value;
        }

        return $data;
    }

    /**
     * Evaluates the condition for the given field.
     *
     * @param SchemaObject $object Object.
     * @param string $field Field name.
     */
    public function evaluate(string $field, SchemaObject $object): bool
    {
        $condition = $this->conditions[$field] ?? null;
        if (!isset($condition)) {
            return true;
        }

        $hash = spl_object_hash($condition);
        if (!isset($this->result[$hash])) {
            $data = $this->extractDataForFields($object, $condition->getFields());
            $this->result[$hash] = $condition->evaluate($data);
        }

        return $this->result[$hash];
    }
}
