<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use App\Schema\SchemaVersion;
use MinVWS\DBCO\Enum\Models\EnumVersion;

interface LocationResolver
{
    public function getPathForSchemaVersion(SchemaVersion $schemaVersion): string;

    public function getUrlForSchemaVersion(SchemaVersion $schemaVersion): string;

    public function getPathForEnumVersion(EnumVersion $enumVersion): string;

    public function getUrlForEnumVersion(EnumVersion $enumVersion): string;
}
