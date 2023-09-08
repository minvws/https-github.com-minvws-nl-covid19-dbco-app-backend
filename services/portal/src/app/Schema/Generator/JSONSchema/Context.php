<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use App\Schema\SchemaVersion;
use MinVWS\DBCO\Enum\Models\EnumVersion;

class Context
{
    public readonly Defs $defs;

    public function __construct(private readonly Config $config)
    {
        $this->defs = new Defs();
    }

    public function getIdForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return $this->config->getIdForSchemaVersion($schemaVersion);
    }

    public function getNameForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return $this->config->getNameForSchemaVersion($schemaVersion);
    }

    public function getRefForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return $this->config->getRefForSchemaVersion($schemaVersion);
    }

    public function getIdForEnumVersion(EnumVersion $enumVersion): string
    {
        return $this->config->getIdForEnumVersion($enumVersion);
    }

    public function getNameForEnumVersion(EnumVersion $enumVersion): string
    {
        return $this->config->getNameForEnumVersion($enumVersion);
    }

    public function getRefForEnumVersion(EnumVersion $enumVersion): string
    {
        return $this->config->getRefForEnumVersion($enumVersion);
    }

    public function getEncodingMode(): ?string
    {
        return $this->config->getEncodingMode();
    }

    public function getUseCompoundSchemas(): UseCompoundSchemas
    {
        return $this->config->getUseCompoundSchemas();
    }
}
