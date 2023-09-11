<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use App\Schema\Schema;

use function array_keys;

/**
 * Base class for schema object class / interface version collectors.
 *
 * @template T of VersionType
 */
abstract class VersionTypeCollector
{
    private Schema $schema;

    /** @var ?array<T> */
    private ?array $items = null;

    public function __construct(Schema $schema)
    {
        $this->schema = $schema;
    }

    protected function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Load reference types.
     *
     * @return array<string, T>
     */
    abstract protected function collect(): array;

    /**
     * Returns the different schema object version interfaces based on the given schema.
     *
     * @return array<string, T> Version interfaces indexed by short name.
     */
    public function getAll(): array
    {
        if ($this->items === null) {
            $this->items = $this->collect();
        }

        return $this->items;
    }

    /**
     * Retyrns the short names for the schema object version reference types.
     *
     * @return array<string>
     */
    public function getAllShortNames(): array
    {
        return array_keys($this->getAll());
    }

    /**
     * Returns the reference type with the given short name.
     *
     * @return T|null
     */
    public function getByShortName(string $shortName): ?VersionType
    {
        return $this->getAll()[$shortName] ?? null;
    }
}
