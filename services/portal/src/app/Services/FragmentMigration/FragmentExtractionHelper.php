<?php

declare(strict_types=1);

namespace App\Services\FragmentMigration;

use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Schema\Schema;
use App\Schema\Types\SchemaType;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use MinVWS\DBCO\Encryption\Security\StorageTerm;

class FragmentExtractionHelper
{
    private static function extractFragmentVersion(
        string $ownerTable,
        string $ownerPKColumn,
        string $ownerFragmentColumn,
        int $ownerSchemaVersion,
        string $targetTable,
        string $targetFKColumn,
        string $targetFragmentName,
        int $targetSchemaVersion,
        StorageTerm $storageTerm,
        string $targetNameColumn = 'name',
        string $targetDataColumn = 'data',
    ): void
    {
        $encryptionInterval = $storageTerm === StorageTerm::long() ? '5 YEAR' : '28 DAY';

        DB::statement(
            "
            INSERT INTO `$targetTable` (
                `$targetFKColumn`,
                $targetNameColumn,
                $targetDataColumn,
                schema_version,
                created_at,
                updated_at,
                expires_at
            )
            SELECT
                o.`$ownerPKColumn`,
                :fragmentName,
                CAST(o.`$ownerFragmentColumn` AS CHAR ASCII),
                :fragmentVersion,
                o.created_at,
                o.updated_at,
                o.created_at + INTERVAL $encryptionInterval
            FROM `$ownerTable` o
            WHERE o.schema_version = :ownerVersion
            AND o.`$ownerFragmentColumn` IS NOT NULL
            ",
            [
                'fragmentName' => $targetFragmentName,
                'fragmentVersion' => $targetSchemaVersion,
                'ownerVersion' => $ownerSchemaVersion,
            ],
        );
    }

    private static function extractFragment(
        Schema $ownerSchema,
        string $ownerTable,
        string $ownerPKColumn,
        string $ownerFragmentColumn,
        string $targetTable,
        string $targetFKColumn,
        StorageTerm $storageTerm,
        string $targetNameColumn = 'name',
        string $targetDataColumn = 'data',
    ): void
    {
        for ($version = $ownerSchema->getMinVersion()->getVersion(); $version <= $ownerSchema->getMaxVersion()->getVersion(); $version++) {
            $ownerSchemaVersion = $ownerSchema->getVersion($version);

            $fieldName = Str::camel($ownerFragmentColumn);
            $field = $ownerSchemaVersion->getField($fieldName);
            if ($field === null) {
                continue;
            }

            $fieldType = $field->getType();
            if (!$fieldType instanceof SchemaType) {
                continue;
            }

            static::extractFragmentVersion(
                $ownerTable,
                $ownerPKColumn,
                $ownerFragmentColumn,
                $version,
                $targetTable,
                $targetFKColumn,
                $fieldType->getSchemaVersion()->getSchema()->getName(),
                $fieldType->getSchemaVersion()->getVersion(),
                $storageTerm,
                $targetNameColumn,
                $targetDataColumn,
            );
        }
    }

    public static function extractCovidCaseFragment(
        string $fragmentNameColumn,
        string $targetNameColumn = 'name',
        string $targetDataColumn = 'data',
    ): void
    {
        static::extractFragment(
            EloquentCase::getSchema(),
            'covidcase',
            'uuid',
            $fragmentNameColumn,
            'case_fragment',
            'case_uuid',
            StorageTerm::long(),
            $targetNameColumn,
            $targetDataColumn,
        );
    }

    public static function extractTaskFragment(
        string $fragmentNameColumn,
        string $targetNameColumn = 'name',
        string $targetDataColumn = 'data',
    ): void
    {
        static::extractFragment(
            EloquentTask::getSchema(),
            'task',
            'uuid',
            $fragmentNameColumn,
            'task_fragment',
            'task_uuid',
            StorageTerm::short(),
            $targetNameColumn,
            $targetDataColumn,
        );
    }

    public static function extractContextFragment(
        string $fragmentNameColumn,
        string $targetNameColumn = 'name',
        string $targetDataColumn = 'data',
    ): void
    {
        static::extractFragment(
            Context::getSchema(),
            'context',
            'uuid',
            $fragmentNameColumn,
            'context_fragment',
            'context_uuid',
            StorageTerm::long(),
            $targetNameColumn,
            $targetDataColumn,
        );
    }

    public static function restoreFragmentData(string $fragmentName, string $columnName): void
    {
        DB::statement('
            UPDATE covidcase
            SET `' . $columnName . '` =
                (SELECT CAST(cf.data AS CHAR ASCII)
                FROM case_fragment cf
                WHERE cf.case_uuid = covidcase.uuid
                  AND cf.fragment_name = \'' . $fragmentName . '\'
                )
          ');

        DB::statement('DELETE FROM `case_fragment` WHERE fragment_name = \' ' . $fragmentName . ' \'');
    }
}
