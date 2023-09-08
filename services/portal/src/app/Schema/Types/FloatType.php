<?php

declare(strict_types=1);

namespace App\Schema\Types;

class FloatType extends ScalarType
{
    protected static string $scalarType = 'float';

    public function getTypeScriptAnnotationType(): string
    {
        return 'number';
    }

    protected function getJSONSchemaType(): string
    {
        return 'number';
    }
}
