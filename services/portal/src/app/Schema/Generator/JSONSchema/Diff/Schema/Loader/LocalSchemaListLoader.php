<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema\Loader;

use function array_map;
use function file_get_contents;
use function glob;
use function preg_replace;
use function sprintf;

class LocalSchemaListLoader extends SchemaListLoader
{
    public function __construct(
        string $schemaBasePath,
        array $schemaNames,
        protected readonly string $rootPath,
    ) {
        parent::__construct($schemaBasePath, $schemaNames);
    }

    protected function getSchemaVersionJson(string $name, int $version): ?string
    {
        $path = $this->rootPath . '/' . $this->getSchemaVersionPath($name, $version);
        return file_get_contents($path) ?: null;
    }

    protected function getSchemaVersionNumbers(string $name): array
    {
        $path = $this->getSchemaBasePath($name);
        $files = glob(sprintf('%s/%s/V*.schema.json', $this->rootPath, $path)) ?: [];

        return array_map(
            static fn (string $file) => (int) preg_replace('|.*/V(\d+)\.schema\.json|', '$1', $file),
            $files,
        );
    }
}
