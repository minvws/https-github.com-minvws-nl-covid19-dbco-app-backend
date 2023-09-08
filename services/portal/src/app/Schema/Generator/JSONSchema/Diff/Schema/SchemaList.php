<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use App\Schema\Generator\JSONSchema\Diff\Model\DiffList;
use App\Schema\Generator\JSONSchema\Diff\Model\DiffType;
use App\Schema\Generator\JSONSchema\Diff\Model\SchemaDiff;
use App\Schema\Generator\JSONSchema\Diff\Model\SchemaListDiff;

use function array_combine;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function ksort;

use const SORT_STRING;

class SchemaList
{
    /** @var array<string, Schema> */
    public readonly array $schemas;

    /**
     * @param array<Schema> $schemas
     */
    public function __construct(array $schemas)
    {
        $indexedSchemas = array_combine(array_map(static fn ($s) => $s->name, $schemas), $schemas);
        ksort($indexedSchemas, SORT_STRING);
        $this->schemas = $indexedSchemas;
    }

    /**
     * @return array<string>
     */
    private function getSchemaNames(): array
    {
        return array_keys($this->schemas);
    }

    private function getSchema(string $schemaName): ?Schema
    {
        return $this->schemas[$schemaName] ?? null;
    }

    public function diff(SchemaList $original): ?SchemaListDiff
    {
        /** @var array<string> $schemaNames */
        $schemaNames = array_unique(array_merge($this->getSchemaNames(), $original->getSchemaNames()));

        /** @var DiffList<string, SchemaDiff> $schemaDiffs */
        $schemaDiffs = new DiffList();
        foreach ($schemaNames as $schemaName) {
            $newSchema = $this->getSchema($schemaName);
            $originalSchema = $original->getSchema($schemaName);

            if (isset($newSchema) && isset($originalSchema)) {
                $diff = $newSchema->diff($originalSchema);
                if ($diff !== null) {
                    $schemaDiffs[$schemaName] = $diff;
                }
            } elseif (isset($newSchema)) {
                $schemaDiffs[$schemaName] = new SchemaDiff(DiffType::Added, $newSchema, null, null);
            } else {
                $schemaDiffs[$schemaName] = new SchemaDiff(DiffType::Removed, null, $originalSchema, null);
            }
        }

        if ($schemaDiffs->isEmpty()) {
            return null;
        }

        return new SchemaListDiff(DiffType::Modified, $this, $original, $schemaDiffs);
    }
}
