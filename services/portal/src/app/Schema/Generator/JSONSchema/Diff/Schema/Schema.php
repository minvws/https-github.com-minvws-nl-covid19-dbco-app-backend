<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffList;
use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\SchemaDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\SchemaVersionDiff;

use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function ksort;

use const SORT_NUMERIC;

class Schema
{
    /** @var array<int, SchemaVersion> $versions */
    public readonly array $versions;

    /**
     * @param array<SchemaVersion> $versions
     */
    public function __construct(public readonly string $name, array $versions)
    {
        $indexedVersions = array_combine(array_map(static fn ($v) => $v->version, $versions), $versions);
        ksort($indexedVersions, SORT_NUMERIC);
        $this->versions = $indexedVersions;
    }

    /**
     * @return array<int>
     */
    private function getVersionNumbers(): array
    {
        return array_keys($this->versions);
    }

    private function getVersion(int $version): ?SchemaVersion
    {
        return $this->versions[$version] ?? null;
    }

    public function diff(Schema $original): ?SchemaDiff
    {
        /** @var array<int> $versionNumbers */
        $versionNumbers = array_unique(array_merge($this->getVersionNumbers(), $original->getVersionNumbers()));

        $versionDiffs = new DiffList();
        foreach ($versionNumbers as $versionNumber) {
            $newVersion = $this->getVersion($versionNumber);
            $originalVersion = $original->getVersion($versionNumber);

            if (isset($newVersion) && isset($originalVersion)) {
                $diff = $newVersion->diff($originalVersion);
                if ($diff !== null) {
                    $versionDiffs[$versionNumber] = $diff;
                }
            } elseif (isset($newVersion)) {
                // try to compare to previous version
                $originalVersion = $original->getVersion($versionNumber - 1);
                $diff = $originalVersion instanceof SchemaVersion ? $newVersion->diff($originalVersion) : null;
                $versionDiffs[$versionNumber] = new SchemaVersionDiff(
                    DiffType::Added,
                    $newVersion,
                    $diff?->original,
                    $diff?->propertyDiffs,
                    $diff?->defDiffs,
                );
            } else {
                $versionDiffs[$versionNumber] = new SchemaVersionDiff(DiffType::Removed, null, $originalVersion, null, null);
            }
        }

        if ($versionDiffs->isEmpty()) {
            return null;
        }

        return new SchemaDiff(DiffType::Modified, $this, $original, $versionDiffs);
    }
}
