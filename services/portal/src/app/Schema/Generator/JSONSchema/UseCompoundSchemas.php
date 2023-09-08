<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

enum UseCompoundSchemas: string
{
    case External = 'external';
    case Internal = 'internal';
    case No = 'no';
}
