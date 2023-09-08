<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema;

use App\Schema\Fields\Field;
use App\Schema\SchemaVersion;

use function strcmp;
use function usort;

/**
 * Can be used to dynamically generate a JSON Schema for a schema version object.
 */
class SchemaVersionBuilder extends AbstractBuilder
{
    public function __construct(private readonly SchemaVersion $schemaVersion)
    {
    }

    protected function buildRoot(Context $context): array
    {
        $schema = parent::buildRoot($context);

        if (!$context->defs->isEmpty()) {
            $schema['$defs'] = [];
            foreach ($context->defs as $id => $def) {
                $schema['$defs'][$id] = $def;
            }
        }

        return $schema;
    }

    protected function buildHeader(Context $context): array
    {
        return [
            '$schema' => "https://json-schema.org/draft/2020-12/schema",
            '$id' => $context->getIdForSchemaVersion($this->schemaVersion),
        ];
    }

    protected function buildBody(Context $context): array
    {
        $schema = ['type' => 'object'];

        if (!empty($this->schemaVersion->getDocumentation()->getLabel())) {
            $schema['title'] = $this->schemaVersion->getDocumentation()->getLabel();
        }

        if (!empty($this->schemaVersion->getDocumentation()->getShortDescription())) {
            $schema['description'] = $this->schemaVersion->getDocumentation()->getShortDescription();
        }

        $fields = $this->schemaVersion->getFields();
        usort($fields, static fn (Field $a, Field $b) => strcmp($a->getName(), $b->getName()));

        $schema['properties'] = [];
        foreach ($fields as $field) {
            if ($context->getEncodingMode() === null || $field->isIncludedInEncode($context->getEncodingMode())) {
                $schema['properties'][$field->getName()] = $field->toJSONSchema($context);
            }
        }

        return $schema;
    }
}
