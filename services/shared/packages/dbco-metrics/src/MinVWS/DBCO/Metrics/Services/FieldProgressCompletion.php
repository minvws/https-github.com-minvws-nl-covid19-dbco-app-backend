<?php

namespace MinVWS\DBCO\Metrics\Services;

/**
 * This is a very simple helper class to track field progress completion.
 */
final class FieldProgressCompletion
{
    private array $hasFields;

    /**
     * @param array $fieldsToTrack
     */
    private function __construct(array $fieldsToTrack)
    {
        foreach ($fieldsToTrack as $fieldKey) {
            $this->hasFields[$fieldKey] = false;
        }
    }

    /**
     * Array with field names that you want to track for completeness.
     *
     * @param array $fieldsToTrack
     * @return FieldProgressCompletion
     */
    public static function create(array $fieldsToTrack): FieldProgressCompletion
    {
        return new FieldProgressCompletion($fieldsToTrack);
    }

    /**
     * Marks a field as complete
     *
     * @param string $field
     * @param string|null $value Checks if the value is complete or not. If null given it will be marked as not complete.
     */
    public function completeCheck(string $field, ?string $value): void
    {
        $this->assertFieldExists($field);

        if ($this->hasFields[$field] === true) {
            // Ignore fields already marked as completed
            return;
        }

        $this->hasFields[$field] = !empty($value);
    }

    /**
     * Checks if a given field is complete. If no field is given it will check if all fields are complete.
     * @param string|null ...$fields
     * @return bool
     */
    public function isComplete(?string ...$fields): bool
    {
        if (empty($fields)) {
            return !in_array(false, $this->hasFields, true);
        }

        foreach ($fields as $field) {
            $this->assertFieldExists($field);

            if (!$this->hasFields[$field]) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $field
     */
    private function assertFieldExists(string $field): void
    {
        if (!isset($this->hasFields[$field])) {
            throw new \InvalidArgumentException(sprintf('Given field %s is not a trackable field', $field));
        }
    }
}
