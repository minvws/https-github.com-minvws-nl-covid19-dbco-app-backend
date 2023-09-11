<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Generator\JSONSchema\Context;

use function is_callable;

/**
 * Subclasses should set their scalar type in the static $scalarType property.
 */
abstract class ScalarType extends Type
{
    protected static string $scalarType = '';

    final public function __construct()
    {
        parent::__construct();

        $this->getValidationRules()
            ->addRule(static::$scalarType)
            ->addRule(fn () => $this->validateType(...));
    }

    public function getScalarType(): string
    {
        return static::$scalarType;
    }

    public function isOfType(mixed $value): bool
    {
        $func = "is_{$this->getScalarType()}";
        return is_callable($func) && $func($value);
    }

    protected function validateType(string $attr, mixed $value, callable $fail): void
    {
        if (!$this->isOfType($value)) {
            $fail("The ' . $attr . ' is not a {$this->getScalarType()}.");
        }
    }

    public function getAnnotationType(): string
    {
        return $this->getScalarType();
    }

    public function getTypeScriptAnnotationType(): string
    {
        return $this->getScalarType();
    }

    protected function getJSONSchemaType(): string
    {
        return $this->getScalarType();
    }

    public function toJSONSchema(Context $context): array
    {
        return ['type' => $this->getJSONSchemaType()];
    }

    public static function createField(string $name): Field
    {
        return new Field($name, new static());
    }

    public static function createArrayField(string $name): ArrayField
    {
        return new ArrayField($name, new static());
    }
}
