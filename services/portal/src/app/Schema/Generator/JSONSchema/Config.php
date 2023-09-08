<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use App\Schema\SchemaVersion;
use MinVWS\DBCO\Enum\Models\EnumVersion;

use function array_map;
use function explode;
use function implode;
use function last;

class Config
{
    private ?string $encodingMode = null;
    private UseCompoundSchemas $useCompoundSchemas = UseCompoundSchemas::No;

    public function __construct(private readonly LocationResolver $locationResolver)
    {
    }

    public function getIdForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return $this->locationResolver->getUrlForSchemaVersion($schemaVersion);
    }

    public function getNameForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return implode(
            '-',
            array_map('ucfirst', explode('.', $schemaVersion->getDocumentationIdentifier())),
        ) . '-V' . $schemaVersion->getVersion();
    }

    public function getRefForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        if ($this->useCompoundSchemas === UseCompoundSchemas::Internal) {
            return '#/$defs/' . $this->getNameForSchemaVersion($schemaVersion);
        }

        return $this->locationResolver->getUrlForSchemaVersion($schemaVersion);
    }

    public function getPathForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return $this->locationResolver->getPathForSchemaVersion($schemaVersion);
    }

    public function getNameForEnumVersion(EnumVersion $enumVersion): string
    {
        $shortName = last(explode('\\', $enumVersion->getEnumClass()));
        return 'Enum-' . $shortName . '-V' . $enumVersion->getVersion();
    }

    public function getIdForEnumVersion(EnumVersion $enumVersion): string
    {
        return $this->locationResolver->getUrlForEnumVersion($enumVersion);
    }

    public function getRefForEnumVersion(EnumVersion $enumVersion): string
    {
        if ($this->useCompoundSchemas === UseCompoundSchemas::Internal) {
            return '#/$defs/' . $this->getNameForEnumVersion($enumVersion);
        }

        return $this->locationResolver->getUrlForEnumVersion($enumVersion);
    }

    public function getPathForEnumVersion(EnumVersion $enumVersion): string
    {
        return $this->locationResolver->getPathForEnumVersion($enumVersion);
    }

    public function setEncodingMode(?string $encodingMode): void
    {
        $this->encodingMode = $encodingMode;
    }

    public function getEncodingMode(): ?string
    {
        return $this->encodingMode;
    }

    public function setUseCompoundSchemas(UseCompoundSchemas $useCompoundSchemas): void
    {
        $this->useCompoundSchemas = $useCompoundSchemas;
    }

    public function getUseCompoundSchemas(): UseCompoundSchemas
    {
        return $this->useCompoundSchemas;
    }
}
