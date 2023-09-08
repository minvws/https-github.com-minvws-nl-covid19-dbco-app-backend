<?php

declare(strict_types=1);

namespace App\Schema\Generator\Base;

use function dirname;
use function file_put_contents;
use function is_dir;
use function mkdir;

/**
 * @template T of VersionType
 */
abstract class VersionTypeCodeGenerator
{
    /** @var T */
    private VersionType $version;

    /**
     * @param T $version
     */
    public function __construct(VersionType $version)
    {
        $this->version = $version;
    }

    /**
     * Returns the version.
     *
     * @return T
     */
    protected function getVersion(): VersionType
    {
        return $this->version;
    }

    /**
     * Returns the relative path.
     */
    abstract public function getPath(): string;

    /**
     * Returns the generated code.
     */
    abstract public function getCode(): string;

    /**
     * Write to file at the given base path.
     */
    public function write(string $basePath): void
    {
        $dir = dirname($basePath . '/' . $this->getPath());
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($basePath . '/' . $this->getPath(), $this->getCode());
    }
}
