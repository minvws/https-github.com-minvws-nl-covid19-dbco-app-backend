<?php

declare(strict_types=1);

namespace App\Schema\Types;

class BoolType extends ScalarType
{
    protected static string $scalarType = 'bool';

    public function valuesEqual(mixed $value1, mixed $value2): bool
    {
        return $value1 === $value2;
    }

    public function getTypeScriptAnnotationType(): string
    {
        return 'boolean';
    }

    protected function getJSONSchemaType(): string
    {
        return 'boolean';
    }
}
