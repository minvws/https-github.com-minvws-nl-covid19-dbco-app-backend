<?php

declare(strict_types=1);

namespace App\Schema;

interface EntityObject extends SchemaObject
{
    /**
     * @param SchemaVersion<static> $schemaVersion
     */
    public function __construct(SchemaVersion $schemaVersion);
}
