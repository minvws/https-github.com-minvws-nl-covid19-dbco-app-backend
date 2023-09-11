<?php

declare(strict_types=1);

namespace App\Schema\Fields;

use App\Schema\SchemaObject;
use App\Schema\Types\SchemaVersionType;

class SchemaVersionField extends DerivedField
{
    public function __construct(string $name = 'schemaVersion')
    {
        parent::__construct($name, new SchemaVersionType(), static fn (SchemaObject $o) => $o->getSchemaVersion()->getVersion());
    }
}
