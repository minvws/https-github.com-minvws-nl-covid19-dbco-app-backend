<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use App\Schema\Schema;
use RuntimeException;
use Throwable;

/**
 * Base class for representing schema version classes and interfaces.
 */
abstract class VersionType
{
    protected Schema $schema;

    /** @var array<VersionInterface> */
    private array $interfaces;

    /**
     * @param array<VersionInterface> $interfaces
     */
    public function __construct(Schema $schema, array $interfaces)
    {
        $this->schema = $schema;
        $this->interfaces = $interfaces;
    }

    /**
     * Returns the schema.
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * @return array<VersionInterface>
     */
    public function getInterfaces(): array
    {
        return $this->interfaces;
    }

    /**
     * Namespace name.
     */
    final public function getNamespaceName(): string
    {
        return $this->schema->getVersionedNamespace();
    }

    /**
     * Version postfix.
     */
    abstract protected function getShortNamePostfix(): string;

    /**
     * Short name (without namespace).
     */
    public function getShortName(): string
    {
        try {
            return $this->schema->getName() . $this->getShortNamePostfix();
        } catch (Throwable $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Full name (including namespace).
     */
    public function getName(): string
    {
        return $this->getNamespaceName() . '\\' . $this->getShortName();
    }
}
