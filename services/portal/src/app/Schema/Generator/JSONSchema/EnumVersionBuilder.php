<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use MinVWS\DBCO\Enum\Models\Enum;
use MinVWS\DBCO\Enum\Models\EnumVersion;

use function array_map;

/**
 * Can be used to dynamically generate a JSON Schema for a schema version object.
 */
class EnumVersionBuilder extends AbstractBuilder
{
    public function __construct(private readonly EnumVersion $enumVersion)
    {
    }

    protected function buildHeader(Context $context): array
    {
        return [
            '$schema' => "https://json-schema.org/draft/2020-12/schema",
            '$id' => $context->getIdForEnumVersion($this->enumVersion),
        ];
    }

    protected function buildBody(Context $context, bool $root = false): array
    {
        return [
            'oneOf' => array_map(
                static fn (Enum $i) => ['const' => $i->value, 'description' => $i->label],
                $this->enumVersion->all(),
            ),
        ];
    }
}
