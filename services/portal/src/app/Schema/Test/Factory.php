<?php

declare(strict_types=1);

namespace App\Schema\Test;

use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaVersion;
use App\Schema\Types\ArrayType;
use App\Schema\Types\EnumVersionType;
use Faker\Generator;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use MinVWS\DBCO\Enum\Models\Enum;
use RuntimeException;

use function array_merge;
use function assert;

/**
 * Factory for creating schema objects with test data.
 *
 * @template T of SchemaObject
 */
class Factory
{
    /** @var Schema<T> */
    private Schema $schema;

    /** @var SchemaVersion<T> */
    private SchemaVersion $schemaVersion;

    protected Generator $faker;

    /**
     * @param Schema<T> $schema
     */
    final public function __construct(Schema $schema)
    {
        $this->schema = $schema;
        $this->schemaVersion = $schema->getCurrentVersion();

        try {
            $faker = Container::getInstance()->make(Generator::class);
            assert($faker instanceof Generator);
            $this->faker = $faker;
        } catch (BindingResolutionException $e) {
            throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Returns the schema for this factory.
     */
    public function getSchema(): Schema
    {
        return $this->schema;
    }

    /**
     * Returns the schema version for this factory.
     */
    public function getSchemaVersion(): SchemaVersion
    {
        return $this->schemaVersion;
    }

    /**
     * Sets the schema version.
     *
     * @return $this
     */
    public function schemaVersion(SchemaVersion $schemaVersion): self
    {
        $this->schemaVersion = $schemaVersion;
        return $this;
    }

    /**
     * Defines the object's default state.
     *
     * @return array
     */
    protected function definition(): array
    {
        return [];
    }

    /**
     * Creates a new instance of the schema object using the default state
     * optionally combined with the given attribute values.
     *
     * @return T
     */
    public function make(array $attributes = []): SchemaObject
    {
        $attributes = array_merge($this->definition(), $attributes);

        /** @var T $object */
        $object = $this->getSchemaVersion()->newInstance();
        foreach ($attributes as $name => $value) {
            $object->$name = $value;
        }

        return $object;
    }

    protected function randomOptionForEnumField(string $name): Enum
    {
        $option = $this->faker->randomElement(
            $this->getSchemaVersion()
                ->getExpectedField($name)
                ->getExpectedType(EnumVersionType::class)
                ->getEnumVersion()
                ->all(),
        );
        assert($option instanceof Enum);
        return $option;
    }

    /**
     * Returns multiple random options for an enum field.
     *
     * @return array<Enum>
     */
    protected function randomOptionsForEnumArrayField(string $name, int $count = 1, bool $allowDuplicates = false): array
    {
        return $this->faker->randomElements(
            $this->getSchemaVersion()
                ->getExpectedField($name)
                ->getExpectedType(ArrayType::class)
                ->getExpectedElementType(EnumVersionType::class)
                ->getEnumVersion()
                ->all(),
            $count,
            $allowDuplicates,
        );
    }
}
