<?php

declare(strict_types=1);

namespace Tests\Helpers;

use App\Schema\SchemaObject;
use App\Schema\SchemaProvider;
use App\Schema\SchemaVersion;
use App\Schema\Types\ArrayType;
use App\Schema\Types\SchemaType;

use function array_slice;
use function assert;
use function count;
use function explode;
use function implode;
use function is_string;

trait QuerySchema
{
    /**
     * @template S of SchemaObject
     * @template T of SchemaProvider<S>&SchemaObject
     *
     * @param class-string<T>|(SchemaProvider<T>&SchemaObject) $schemaProviderClassOrInstance
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function getFieldVersion(
        string|SchemaProvider|SchemaObject $schemaProviderClassOrInstance,
        string $field,
        ?int $schemaVersion = null,
    ): int {
        $schemaProviderInstance = is_string($schemaProviderClassOrInstance)
            ? new $schemaProviderClassOrInstance()
            : $schemaProviderClassOrInstance;

        return $this->getFieldVersionFromSchemaProvider($schemaProviderInstance, $field, $schemaVersion);
    }

    /**
     * @template T of SchemaObject
     *
     * @param SchemaProvider<T>&SchemaObject $schemaProvider
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function getFieldVersionFromSchemaProvider(
        SchemaProvider&SchemaObject $schemaProvider,
        string $field,
        ?int $schemaVersion = null,
    ): int {
        $schemaVersionObject = $schemaVersion === null
            ? $schemaProvider->getSchemaVersion()
            : $schemaProvider::getSchema()->getVersion($schemaVersion);

        assert($schemaVersionObject instanceof SchemaVersion);

        return $this->getFieldVersionFromSchemaVersion($schemaVersionObject, $field);
    }

    /**
     * @template T of SchemaObject
     *
     * @param SchemaVersion<T> $schemaVersion
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function getFieldVersionFromSchemaVersion(SchemaVersion $schemaVersion, string $field): int
    {
        return $this->getFieldSchemaVersion($schemaVersion, $field)->getVersion();
    }

    /**
     * @template T of SchemaObject
     *
     * @param SchemaVersion<T> $schemaVersion
     * @param string $field Can be a dot separated string to get a nested field
     */
    private function getFieldSchemaVersion(SchemaVersion $schemaVersion, string $field): SchemaVersion
    {
        $fields = explode('.', $field);
        if (count($fields) > 1) {
            $schemaVersion = $this->getFieldSchemaVersion($schemaVersion, $fields[0]);

            return $this->getFieldSchemaVersion($schemaVersion, implode('.', array_slice($fields, 1)));
        }

        $type = $schemaVersion
            ->getExpectedField($field)
            ->getType();

        assert($type instanceof SchemaType || $type instanceof ArrayType);

        if ($type instanceof ArrayType) {
            $type = $type->getExpectedElementType(SchemaType::class);
        }

        return $type->getSchemaVersion();
    }
}
