<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema\Loader;

use function escapeshellarg;
use function exec;
use function is_string;
use function preg_match;
use function shell_exec;

/**
 * @codeCoverageIgnore
 */
class GitSchemaListLoader extends SchemaListLoader
{
    protected function __construct(
        string $schemaBasePath,
        array $schemaNames,
        protected readonly string $tagOrBranch,
    ) {
        parent::__construct($schemaBasePath, $schemaNames);
    }

    protected function getSchemaVersionJson(string $name, int $version): ?string
    {
        $path = $this->getSchemaVersionPath($name, $version);
        $command = 'git show ' . escapeshellarg($this->tagOrBranch) . ':' . escapeshellarg($path);
        $json = shell_exec($command);
        return is_string($json) ? $json : null;
    }

    protected function getSchemaVersionNumbers(string $name): array
    {
        $path = $this->getSchemaBasePath($name);
        $command = 'git show ' . escapeshellarg($this->tagOrBranch) . ':' . escapeshellarg($path) . ' 2>&1';
        exec($command, $lines, $exitCode);

        if ($exitCode !== 0) {
            return [];
        }

        $versions = [];
        foreach ($lines as $line) {
            if (preg_match('/V(\d+)\.schema\.json/', $line, $matches)) {
                $versions[] = (int) $matches[1];
            }
        }

        return $versions;
    }
}
