<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Generator\JSONSchema\Context;
use App\Schema\Generator\JSONSchema\SchemaVersionBuilder;
use App\Schema\Generator\JSONSchema\UseCompoundSchemas;
use App\Schema\Schema;
use App\Schema\SchemaObject;
use App\Schema\SchemaVersion;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;

use function array_slice;
use function assert;
use function call_user_func;
use function explode;
use function is_null;

class SchemaType extends Type
{
    private SchemaVersion $schemaVersion;

    public function __construct(SchemaVersion|Schema $schemaVersion)
    {
        parent::__construct();

        $this->schemaVersion = $schemaVersion instanceof Schema ? $schemaVersion->getCurrentVersion() : $schemaVersion;

        $this->getValidationRules()
            ->addChild($this->schemaVersion->getValidationRules());
    }

    public function isOfType(mixed $value): bool
    {
        return $value instanceof SchemaObject && $value->getSchemaVersion()->isEqual($this->getSchemaVersion());
    }

    public function getSchema(): Schema
    {
        return $this->schemaVersion->getSchema();
    }

    /**
     * Returns the schema version info.
     */
    public function getSchemaVersion(): SchemaVersion
    {
        return $this->schemaVersion;
    }

    public function encode(EncodingContainer $container, mixed $value): void
    {
        $encoder = $this->getEncoder($container->getContext()->getMode());
        if ($encoder !== null) {
            call_user_func($encoder, $container, $value);
        } elseif ($value === null) {
            $container->encodeNull();
        } else {
            assert($value instanceof SchemaObject);
            $this->getSchemaVersion()->encode($container, $value);
        }
    }

    public function decode(DecodingContainer $container, mixed $current): ?SchemaObject
    {
        if ($container->isPresent()) {
            assert(is_null($current) || $current instanceof SchemaObject);
            return $this->getSchemaVersion()->decode($container, $current);
        }

        return null;
    }

    public function getAnnotationType(): string
    {
        return '\\' . $this->getSchemaVersion()->getClass();
    }

    public function getTypeScriptAnnotationType(): string
    {
        // Return only the class name, during code generation, the correct TS interface needs to be imported
        return array_slice(explode('\\', $this->getSchemaVersion()->getClass()), -1)[0];
    }

    public function toJSONSchema(Context $context): array
    {
        $ref = $context->getRefForSchemaVersion($this->schemaVersion);
        if ($context->getUseCompoundSchemas() === UseCompoundSchemas::No) {
            return ['$ref' => $ref];
        }

        $name = $context->getNameForSchemaVersion($this->schemaVersion);
        if (!$context->defs->contains($name)) {
            $builder = new SchemaVersionBuilder($this->schemaVersion);
            $context->defs->put($name, $builder->buildDef($context));
        }

        return ['$ref' => $ref];
    }

    public function valuesEqual(mixed $value1, mixed $value2): bool
    {
        if ($value1 === null && $value2 === null) {
            return true;
        }

        if (!$value1 instanceof SchemaObject || !$this->isOfType($value1)) {
            return false;
        }

        if (!$value2 instanceof SchemaObject || !$this->isOfType($value2)) {
            return false;
        }

        foreach ($this->getSchemaVersion()->getFields() as $field) {
            if (!$field->valuesForObjectsEqual($value1, $value2)) {
                return false;
            }
        }

        return true;
    }
}
