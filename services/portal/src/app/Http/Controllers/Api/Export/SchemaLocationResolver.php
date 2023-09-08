<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Export;

use App\Schema\Generator\JSONSchema\LocationResolver;
use App\Schema\SchemaVersion;
use MinVWS\DBCO\Enum\Models\EnumVersion;

use function array_map;
use function explode;
use function implode;
use function last;
use function route;
use function rtrim;

class SchemaLocationResolver implements LocationResolver
{
    private string $basePath;

    public function __construct(string $basePath)
    {
        $this->basePath = rtrim($basePath, '/');
    }

    private function getRelativePathForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        $name = implode('/', array_map('ucfirst', explode('.', $schemaVersion->getDocumentationIdentifier())));
        return $name . '/V' . $schemaVersion->getVersion();
    }

    public function getPathForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return $this->basePath . '/' . $this->getRelativePathForSchemaVersion($schemaVersion) . '.schema.json';
    }

    public function getUrlForSchemaVersion(SchemaVersion $schemaVersion): string
    {
        return route('api-export-json-schema', ['path' => $this->getRelativePathForSchemaVersion($schemaVersion)], false);
    }

    private function getRelativePathForEnumVersion(EnumVersion $enumVersion): string
    {
        $shortName = last(explode('\\', $enumVersion->getEnumClass()));
        return 'enums/' . $shortName . '/V' . $enumVersion->getVersion();
    }

    public function getPathForEnumVersion(EnumVersion $enumVersion): string
    {
        return $this->basePath . '/' . $this->getRelativePathForEnumVersion($enumVersion) . '.schema.json';
    }

    public function getUrlForEnumVersion(EnumVersion $enumVersion): string
    {
        return route('api-export-json-schema', ['path' => $this->getRelativePathForEnumVersion($enumVersion)], false);
    }
}
