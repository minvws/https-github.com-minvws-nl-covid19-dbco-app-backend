<?php

declare(strict_types=1);

namespace App\Schema;

/**
 * A factory for creating new schema object instances for a given schema version.
 *
 * @template T of SchemaObject
 */
interface SchemaObjectFactory
{
    /**
     * Create a new instance for the given schema version.
     *
     * *** Should not be used directly, use the SchemaVersion::newInstance() method instead! ***
     *
     * @param SchemaVersion<T> $schemaVersion
     *
     * @return T
     */
    public static function newUninitializedInstanceWithSchemaVersion(SchemaVersion $schemaVersion): SchemaObject;
}
