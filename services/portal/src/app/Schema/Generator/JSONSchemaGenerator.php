<?php

declare(strict_types=1);

namespace App\Schema\Generator;

use App\Schema\Generator\JSONSchema\Config;
use App\Schema\Generator\JSONSchema\EnumVersionBuilder;
use App\Schema\Generator\JSONSchema\SchemaVersionBuilder;
use App\Schema\Schema;
use App\Schema\SchemaVersion;
use MinVWS\DBCO\Enum\Models\Enum;
use MinVWS\DBCO\Enum\Models\EnumVersion;

use function dirname;
use function file_put_contents;
use function is_dir;
use function json_encode;
use function mkdir;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

class JSONSchemaGenerator
{
    private function writeToFile(array $schema, string $path): void
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        $json = json_encode($schema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        file_put_contents($path, $json);
    }

    public function generateForSchemaVersion(SchemaVersion $version, Config $config): void
    {
        $builder = new SchemaVersionBuilder($version);
        $schema = $builder->build($config);
        $this->writeToFile($schema, $config->getPathForSchemaVersion($version));
    }

    public function generateForSchema(Schema $schema, Config $config): void
    {
        for ($version = $schema->getMinVersion()->getVersion(); $version <= $schema->getMaxVersion()->getVersion(); $version++) {
            $this->generateForSchemaVersion($schema->getVersion($version), $config);
        }
    }

    public function generateForEnumVersion(EnumVersion $version, Config $config): void
    {
        $builder = new EnumVersionBuilder($version);
        $schema = $builder->build($config);
        $this->writeToFile($schema, $config->getPathForEnumVersion($version));
    }

    /**
     * @param class-string<Enum> $enum
     */
    public function generateForEnum(string $enum, Config $config): void
    {
        for ($version = $enum::getMinVersion()->getVersion(); $version <= $enum::getMaxVersion()->getVersion(); $version++) {
            $this->generateForEnumVersion($enum::getVersion($version), $config);
        }
    }
}
