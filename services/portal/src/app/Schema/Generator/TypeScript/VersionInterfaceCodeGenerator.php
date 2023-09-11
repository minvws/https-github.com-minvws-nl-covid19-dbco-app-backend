<?php

declare(strict_types=1);

namespace App\Schema\Generator\TypeScript;

use App\Schema\Fields\ArrayField;
use App\Schema\Generator\Base\VersionInterface;
use App\Schema\Types\EnumVersionType;
use App\Schema\Types\SchemaType;

use function array_filter;
use function array_map;
use function array_push;
use function array_slice;
use function asort;
use function explode;
use function implode;
use function in_array;
use function lcfirst;
use function str_replace;
use function strlen;
use function ucfirst;

/**
 * Represents an interface a schema object can implement for a certain version.
 *
 * @extends VersionTypeCodeGenerator<VersionInterface>
 */
class VersionInterfaceCodeGenerator extends VersionTypeCodeGenerator
{
    /**
     * Schema interface that should be imported for the fields.
     */
    private function getSchemaImports(): string
    {
        $imports = [];

        foreach ($this->getVersion()->getFields() as $field) {
            if (!$field->isIncludedInDecode(null)) {
                continue;
            }

            $type = $field instanceof ArrayField ? $field->getElementType() : $field->getType();
            if (!($type instanceof SchemaType)) {
                continue;
            }

            $phpClass = $type->getSchemaVersion()->getClass();
            $tsInterface = array_slice(explode('\\', $phpClass), -1)[0];
            $path = str_replace('App\\Models\\Versions\\', '', $phpClass);
            $path = implode('/', array_map(static fn (string $part) => lcfirst($part), explode('\\', $path)));

            $import = "import { " . $tsInterface . " } from '@dbco/schema/" . $path . "';";

            if (!in_array($import, $imports, true)) {
                array_push($imports, $import);
            }
        }

        asort($imports);

        return implode("\n", $imports);
    }

    /**
     * Enums that should be imported.
     */
    protected function getEnumImports(): string
    {
        $imports = [];
        foreach ($this->getVersion()->getFields() as $field) {
            if (!$field->isIncludedInDecode(null)) {
                continue;
            }

            $type = $field instanceof ArrayField ? $field->getElementType() : $field->getType();
            if (!($type instanceof EnumVersionType)) {
                continue;
            }

            $className = $type->getEnumVersion()->getEnumClass();
            $tsEnum = $className::tsConst() . "V" . $type->getEnumVersion()->getVersion();
            if (!in_array($tsEnum, $imports, true)) {
                array_push($imports, $tsEnum);
            }
        }

        asort($imports);

        return implode("\n", array_map(static fn (string $e) => "import { " . ucfirst($e) . " } from '@dbco/enum';", $imports));
    }

    public function getCode(): string
    {
        $code = "/**\n * *** WARNING ***\n * This code is auto-generated. Any changes will be reverted by generating the schema!\n */\n\n";

        $code .= "import { DTO } from '@dbco/schema/dto';\n";

        $enumImports = $this->getEnumImports();
        $schemaImports = $this->getSchemaImports();
        if (strlen($enumImports) > 0 || strlen($schemaImports) > 0) {
            $code .= $schemaImports ? $schemaImports . "\n" : '';
            $code .= $enumImports ? $enumImports . "\n" : '';
            $code .= "\n";
        }

        $code .= "/**\n * {$this->getVersion()->getShortName()}\n */\n";
        $code .= "export interface {$this->getVersion()->getShortName()} {";

        $includedFields = array_filter($this->getVersion()->getFields(), static fn($field) => $field->isIncludedInDecode(null));
        if ($includedFields) {
            $code .= "\n";
            $code .= implode("", array_map(static fn($field) => "    {$field->getTypeScriptAnnotation()};\n", $includedFields));
        }

        $code .= "}\n";
        $code .= "\n";
        $code .= "export type {$this->getVersion()->getShortName()}DTO = DTO<{$this->getVersion()->getShortName()}>;\n";

        return $code;
    }
}
