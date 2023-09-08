<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Generator\JSONSchema\Context;
use MinVWS\Codable\DecodingContainer;

use function is_object;

class AnyType extends Type
{
    private string $annotationType;
    private string $typeScriptAnnotationType;

    final public function __construct(string $annotationType = 'mixed', string $typeScriptAnnotationType = 'any')
    {
        parent::__construct();

        $this->annotationType = $annotationType;
        $this->typeScriptAnnotationType = $typeScriptAnnotationType;
    }

    public function isOfType(mixed $value): bool
    {
        return true;
    }

    public function decode(DecodingContainer $container, mixed $current): ?object
    {
        $obj = $container->decodeIfPresent(null, null, $current);

        if (is_object($obj)) {
            return $obj;
        }

        return null;
    }

    public function getAnnotationType(): string
    {
        return $this->annotationType;
    }

    public function getTypeScriptAnnotationType(): string
    {
        return $this->typeScriptAnnotationType;
    }

    public function toJSONSchema(Context $context): array
    {
        return [];
    }

    /**
     * Create field with object type for the given class.
     */
    public static function createField(string $name, string $annotationType = 'mixed', string $typeScriptAnnotationType = 'any'): Field
    {
        return new Field($name, new static($annotationType, $typeScriptAnnotationType));
    }

    /**
     * Create array field with object type for the given class.
     */
    public static function createArrayField(string $name, string $annotationType, string $typeScriptAnnotationType): ArrayField
    {
        return new ArrayField($name, new static($annotationType, $typeScriptAnnotationType));
    }
}
