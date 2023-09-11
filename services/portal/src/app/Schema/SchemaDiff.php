<?php

declare(strict_types=1);

namespace App\Schema;

use App\Schema\Fields\Field;

use function assert;

class SchemaDiff
{
    public const UNMODIFIED = 'unmodified';
    public const ADDED = 'added';
    public const REMOVED = 'removed';

    private SchemaVersion $version1;

    private SchemaVersion $version2;

    public function __construct(SchemaVersion $version1, SchemaVersion $version2)
    {
        assert($version1->getSchema()->getClass() === $version2->getSchema()->getClass());
        $this->version1 = $version1;
        $this->version2 = $version2;
    }

    public function getVersion1(): SchemaVersion
    {
        return $this->version1;
    }

    public function getVersion2(): SchemaVersion
    {
        return $this->version2;
    }

    /**
     * Returns the fields that have been added in version 2 in regard to version 1.
     *
     * @return array
     */
    public function getAddedFields(): array
    {
        $fields = [];

        foreach ($this->version2->getFields() as $field) {
            if (!$field->isInVersion($this->version1->getVersion())) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns the fields that have been removed in version 2 in regard to version 1.
     *
     * @return array
     */
    public function getRemovedFields(): array
    {
        $fields = [];

        foreach ($this->version1->getFields() as $field) {
            if (!$field->isInVersion($this->version2->getVersion())) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns the fields that are both part of version 1 and version 2.
     *
     * @return array
     */
    public function getUnmodifiedFields(): array
    {
        $fields = [];

        foreach ($this->version1->getSchema()->getFields() as $field) {
            if ($field->isInVersion($this->version1->getVersion()) && $field->isInVersion($this->version2->getVersion())) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns all fields that are part of either version 1 or version 2.
     *
     * @return array
     */
    public function getAllFields(): array
    {
        $fields = [];

        foreach ($this->version1->getSchema()->getFields() as $field) {
            if ($field->isInVersion($this->version1->getVersion()) || $field->isInVersion($this->version2->getVersion())) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Returns the diff result for the given field.
     *
     * @param Field $field Field.
     */
    public function getResultForField(Field $field): string
    {
        if ($field->isInVersion($this->version1->getVersion()) && $field->isInVersion($this->version2->getVersion())) {
            return self::UNMODIFIED;
        }

        if ($field->isInVersion($this->version2->getVersion())) {
            return self::ADDED;
        }

        return self::REMOVED;
    }
}
