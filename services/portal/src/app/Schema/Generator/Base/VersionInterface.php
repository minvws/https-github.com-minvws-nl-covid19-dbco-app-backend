<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use App\Schema\Fields\Field;
use App\Schema\Fields\SchemaVersionField;
use App\Schema\Schema;

use function array_combine;
use function array_filter;
use function array_keys;
use function array_map;
use function count;

/**
 * Represents an interface a schema object can implement for a certain version.
 */
abstract class VersionInterface extends VersionType
{
    private int $minVersion;

    /** @var array<string, Field>|null */
    private ?array $fields = null;

    /**
     * @param array<VersionInterface> $interfaces
     */
    public function __construct(Schema $schema, int $minVersion, array $interfaces)
    {
        parent::__construct($schema, $interfaces);

        $this->minVersion = $minVersion;
    }

    /**
     * Returns the minimum schema version for this interface.
     */
    public function getMinVersion(): int
    {
        return $this->minVersion;
    }

    /**
     * Returns the maximum schema version for this interface.
     */
    public function getMaxVersion(): ?int
    {
        return null;
    }

    /**
     * Returns the fields indexed by name.
     *
     * @return array<string, Field>
     */
    private function loadFields(): array
    {
        $minVersion = $this->schema->getVersion($this->getMinVersion());
        $maxVersion = $this->getMaxVersion() !== null ? $this->schema->getVersion($this->getMaxVersion()) : $this->schema->getMaxVersion();

        $fields = array_filter(
            $minVersion->diff($maxVersion)->getUnmodifiedFields(),
            fn (Field $field) => !$field instanceof SchemaVersionField && $field->getMinVersion() === $this->getMinVersion() && $field->getMaxVersion() === $this->getMaxVersion()
        );

        /** @var array<string, Field> $result */
        $result = array_combine(array_map(static fn ($f) => $f->getName(), $fields), $fields);
        return $result;
    }

    /**
     * Returns the fields indexed by name.
     *
     * @return array<string, Field>
     */
    final public function getFields(): array
    {
        if ($this->fields === null) {
            $this->fields = $this->loadFields();
        }

        return $this->fields;
    }

    /**
     * Returns the field names.
     *
     * @return array
     */
    final public function getFieldNames(): array
    {
        return array_keys($this->getFields());
    }

    /**
     * Returns the field with the given name.
     */
    final public function getField(string $name): ?Field
    {
        return $this->getFields()[$name] ?? null;
    }

    /**
     * Doesn't this interface contain fields (on its own)?
     */
    final public function isEmpty(): bool
    {
        return count($this->getFields()) === 0;
    }
}
