<?php

declare(strict_types=1);

namespace App\Schema;

use App\Schema\Documentation\Traits\HasDocumentation;
use App\Schema\Fields\Field;
use App\Schema\Fields\SchemaVersionField;
use App\Schema\Purpose\Traits\HasPurposeSpecification;
use App\Schema\Test\Factory;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionException;
use RuntimeException;

use function array_filter;
use function array_reduce;
use function array_values;
use function assert;
use function class_exists;
use function is_a;
use function lcfirst;
use function max;
use function range;
use function rtrim;

/**
 * Describes the schema / fields for a class.
 *
 * A schema can have multiple versions based on the min and max versions defined for the fields in the schema.
 *
 * @template T of SchemaObject
 */
class Schema
{
    use HasDocumentation;
    use HasPurposeSpecification;

    /** @var class-string<T> */
    private string $class;

    private int $currentVersion = 1;

    /** @var array<Field> */
    private array $fields = [];

    private ?SchemaVersionField $schemaVersionField = null;

    private ?string $name = null;

    private ?string $ownerFieldName = null;

    private bool $useVersionedClasses = false;

    private ?string $versionedNamespace = null;

    /** @var callable|class-string<SchemaObjectFactory>|null */
    private $objectFactory = null;

    /** @var class-string<Factory>|null */
    private ?string $testFactoryClass = null;

    /** @var array<int, SchemaVersion<T>> */
    private array $schemaVersions = [];

    private ?int $maxVersion = null;

    /**
     * @param class-string<T> $class
     */
    public function __construct(string $class, bool $versioned = true)
    {
        $this->class = $class;

        if (is_a($class, Entity::class, true)) {
            $this->ownerFieldName = 'owner';
        }

        if (!$versioned) {
            return;
        }

        $this->schemaVersionField = new SchemaVersionField();
        $this->add($this->schemaVersionField);
    }

    /**
     * Returns the object factory, if set.
     *
     * By default, if no factory is provided, the system will check if the schema class implements the SchemaObjectFactory
     * interface and will use late-static-binding to call the factory method. This method will return null.
     *
     * @return callable|class-string<SchemaObjectFactory<T>>|null
     */
    public function getObjectFactory()
    {
        return $this->objectFactory;
    }

    /**
     * Sets a factory callback for creating new objects based on this schema.
     *
     * By default, if no factory is provided, the system will check if the schema class implements the SchemaObjectFactory
     * interface and will use late-static-binding to call the factory method.
     *
     * @param callable|class-string<SchemaObjectFactory<T>>|null $factory
     */
    public function setObjectFactory($factory): void
    {
        $this->objectFactory = $factory;
    }

    /**
     * Schema object class.
     *
     * @return class-string<T>
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * Returns the name for this schema.
     *
     * Default to the class short name.
     */
    public function getName(): string
    {
        if (empty($this->name)) {
            $refClass = new ReflectionClass($this->getClass());
            return $refClass->getShortName();
        }

        return $this->name;
    }

    /**
     * Set the name for this schema.
     *
     * This is used for documentation purposes and as basename for versioned classes / interfaces.
     *
     * If not explicitly set, the system will default to the class short name.
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns the name for the field that exposes the owner entity/model.
     */
    public function getOwnerFieldName(): ?string
    {
        return $this->ownerFieldName;
    }

    /**
     * Sets the name / alias for the field that exposes the owner entity/model.
     */
    public function setOwnerFieldName(string $name): void
    {
        $this->ownerFieldName = $name;
    }

    protected function getDefaultDocumentationIdentifier(): string
    {
        return lcfirst($this->getName());
    }

    /**
     * @inheritdoc
     */
    protected function getDocumentationIdentifiers(): array
    {
        return ['schema.' . $this->getDocumentationIdentifier(), $this->getDocumentationIdentifier()];
    }

    /**
     * Returns the given version of the schema.
     *
     * @return SchemaVersion<T>
     */
    public function getVersion(int $version): SchemaVersion
    {
        if ($version < 1 || $version > $this->getMaxVersionInt()) {
            throw new InvalidArgumentException('Invalid version ' . $version . ' for ' . $this);
        }

        if (!isset($this->schemaVersions[$version])) {
            $this->schemaVersions[$version] = new SchemaVersion($this, $version);
        }

        return $this->schemaVersions[$version];
    }

    /**
     * Returns the first version.
     *
     * @return SchemaVersion<T>
     */
    public function getMinVersion(): SchemaVersion
    {
        return $this->getVersion(1);
    }

    /**
     * Returns the maximum version as int.
     */
    private function getMaxVersionInt(): int
    {
        if (!isset($this->maxVersion)) {
            $this->maxVersion = max(
                $this->currentVersion,
                array_reduce(
                    $this->getFields(),
                    static fn ($max, Field $field) => max($max, $field->getMinVersion(), $field->getMaxVersion() + 1),
                    1,
                ),
            );
        }

        return $this->maxVersion;
    }

    /**
     * Returns the maximum version.
     *
     * @return SchemaVersion<T>
     */
    public function getMaxVersion(): SchemaVersion
    {
        return $this->getVersion($this->getMaxVersionInt());
    }

    /**
     * Returns the current version.
     *
     * This isn't necessarily the maximum version defined.
     *
     * @return SchemaVersion<T>
     */
    public function getCurrentVersion(): SchemaVersion
    {
        return $this->getVersion($this->currentVersion);
    }

    /**
     * Sets the current version.
     */
    public function setCurrentVersion(int $currentVersion): void
    {
        $this->currentVersion = $currentVersion;
        $this->maxVersion = null;
    }

    /**
     * Is using generated version classes / interfaces?
     */
    public function isUsingVersionedClasses(): bool
    {
        return $this->useVersionedClasses;
    }

    /**
     * Use generated version classes / interfaces.
     */
    public function setUseVersionedClasses(bool $useVersionedClasses): void
    {
        $this->useVersionedClasses = $useVersionedClasses;
    }

    /**
     * Sets the namespace for generated version classes / interfaces.
     */
    public function getVersionedNamespace(): string
    {
        if (empty($this->versionedNamespace)) {
            $refClass = new ReflectionClass($this->getClass());
            return $refClass->getNamespaceName();
        }

        return rtrim($this->versionedNamespace, '\\');
    }

    /**
     * Use versioned namespace.
     */
    public function setVersionedNamespace(?string $versionedNamespace): void
    {
        $this->versionedNamespace = $versionedNamespace;
    }

    /**
     * Add field to schema.
     *
     * @template F of Field
     *
     * @param F $field
     *
     * @return F
     */
    public function add(Field $field): Field
    {
        if ($this->schemaVersionField !== null && $field instanceof SchemaVersionField) {
            $this->fields = array_filter($this->fields, static fn (Field $f) => !($f instanceof SchemaVersionField));
            $this->schemaVersionField = $field;
        }

        $this->fields[] = $field;
        $field->setSchema($this);

        $this->maxVersion = null;

        return $field;
    }

    /**
     * Returns all the fields in the schema.
     *
     * These fields are not filtered by version. To filter the fields for a certain
     * version use the getVersion(...) method first.
     *
     * @return array<Field>
     */
    public function getFields(?callable $filter = null): array
    {
        if ($filter === null) {
            return $this->fields;
        }

        return array_values(array_filter($this->fields, $filter));
    }

    /**
     * Returns the schema version field.
     */
    public function getSchemaVersionField(): ?SchemaVersionField
    {
        return $this->schemaVersionField;
    }

    /**
     * Returns the factory class name.
     *
     * If not explicitly set, the system will look in the nested namespace "Factories"
     * for a class named "<Class>Factory" or will fallback to the factory base class.
     */
    public function getTestFactoryClass(): string
    {
        if (isset($this->testFactoryClass)) {
            return $this->testFactoryClass;
        }

        try {
            $refClass = new ReflectionClass($this->getClass());
            $testFactoryClass = $refClass->getNamespaceName() . '\\Factories\\' . $refClass->getShortName() . 'Factory';
            if (class_exists($testFactoryClass)) {
                return $testFactoryClass;
            }
        } catch (ReflectionException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        return Factory::class;
    }

    /**
     * Sets the factory class to be used for generating objects with test data.
     *
     * By default, the system will look in the nested namespace "Factories"
     * for a class named "<Class>Factory".
     *
     * @param class-string<Factory> $class
     */
    public function setTestFactoryClass(string $class): void
    {
        $this->testFactoryClass = $class;
    }

    /**
     * Returns a factory instance for generating objects with test data.
     */
    public function getTestFactory(): Factory
    {
        $class = $this->getTestFactoryClass();
        $factory = new $class($this);
        assert($factory instanceof Factory);
        return $factory;
    }

    /**
     * String representation.
     */
    public function __toString(): string
    {
        return static::class . '<' . $this->getClass() . '>';
    }

    /**
     * @return array<int>
     */
    public function getVersions(): array
    {
        return range($this->getMinVersion()->getVersion(), $this->getMaxVersionInt());
    }
}
