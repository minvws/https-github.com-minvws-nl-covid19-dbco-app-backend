<?php

declare(strict_types=1);

namespace App\Schema;

/**
 * An object that supports a schema.
 */
interface SchemaObject
{
    /**
     * Returns the schema version for this object.
     *
     * @return SchemaVersion<static>
     */
    public function getSchemaVersion(): SchemaVersion;
}
