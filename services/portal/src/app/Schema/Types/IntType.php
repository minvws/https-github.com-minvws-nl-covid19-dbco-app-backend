<?php

declare(strict_types=1);

namespace App\Schema\Types;

class IntType extends ScalarType
{
    protected static string $scalarType = 'int';

    public function getTypeScriptAnnotationType(): string
    {
        return 'number';
    }

    protected function getJSONSchemaType(): string
    {
        return 'integer';
    }
}
