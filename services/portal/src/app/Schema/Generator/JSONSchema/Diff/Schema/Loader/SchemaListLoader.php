<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema\Loader;

use App\Schema\Generator\JSONSchema\Diff\Schema\Schema;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaList;
use App\Schema\Generator\JSONSchema\Diff\Schema\SchemaVersion;
use MinVWS\Codable\JSONDecoder;

use function assert;
use function count;
use function is_string;
use function sort;
use function sprintf;

use const SORT_NUMERIC;

abstract class SchemaListLoader
{
    public function __construct(
        protected readonly string $schemaBasePath,
        protected readonly array $schemaNames,
    ) {
    }

    abstract protected function getSchemaVersionJson(string $name, int $version): ?string;

    abstract protected function getSchemaVersionNumbers(string $name): array;

    protected function getSchemaBasePath(string $name): string
    {
        return sprintf('%s/%s', $this->schemaBasePath, $name);
    }

    protected function getSchemaVersionPath(string $name, int $version): string
    {
        return sprintf('%s/V%d.schema.json', $this->getSchemaBasePath($name), $version);
    }

    private function getSchemaVersion(string $name, int $version): SchemaVersion
    {
        $json = $this->getSchemaVersionJson($name, $version);
        assert(is_string($json));
        $decoder = new JSONDecoder();
        $schemaVersion = $decoder->decode($json)->decodeObject(SchemaVersion::class);
        assert($schemaVersion instanceof SchemaVersion);
        return $schemaVersion;
    }

    private function getSchemaVersions(string $name): array
    {
        $versionNumbers = $this->getSchemaVersionNumbers($name);
        sort($versionNumbers, SORT_NUMERIC);

        $versions = [];
        foreach ($versionNumbers as $versionNumber) {
            $versions[] = $this->getSchemaVersion($name, $versionNumber);
        }

        return $versions;
    }

    protected function load(): SchemaList
    {
        $schemas = [];

        foreach ($this->schemaNames as $name) {
            $versions = $this->getSchemaVersions($name);
            if (count($versions) > 0) {
                $schemas[] = new Schema($name, $versions);
            }
        }

        return new SchemaList($schemas);
    }

    public static function loadLocal(string $schemaBasePath, array $schemaNames, string $rootPath): SchemaList
    {
        $loader = new LocalSchemaListLoader($schemaBasePath, $schemaNames, $rootPath);
        return $loader->load();
    }

    /**
     * @codeCoverageIgnore
     */
    public static function loadFromGit(string $schemaBasePath, array $schemaNames, string $tagOrBranch): SchemaList
    {
        $loader = new GitSchemaListLoader($schemaBasePath, $schemaNames, $tagOrBranch);
        return $loader->load();
    }
}
