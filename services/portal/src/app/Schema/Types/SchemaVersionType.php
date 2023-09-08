<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Fields\Field;
use App\Schema\Fields\SchemaVersionField;

class SchemaVersionType extends IntType
{
    public static function createField(string $name): Field
    {
        return new SchemaVersionField($name);
    }
}
