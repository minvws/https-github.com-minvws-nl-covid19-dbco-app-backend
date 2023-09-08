<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Generator\JSONSchema\Context;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\ValueNotFoundException;

use function assert;
use function is_a;
use function is_null;
use function is_object;
use function is_string;

class ObjectType extends Type
{
    /** @var class-string */
    private string $class;

    /**
     * @param class-string $class
     */
    final public function __construct(string $class)
    {
        parent::__construct();

        $this->class = $class;

        $this->getValidationRules()->addFatal('array');
    }

    /**
     * Returns the codable class.
     *
     * @return class-string
     */
    private function getClass(): string
    {
        return $this->class;
    }

    public function isOfType(mixed $value): bool
    {
        if (!(is_string($value) || is_object($value))) {
            return false;
        }

        return is_a($value, $this->getClass(), true);
    }

    public function decode(DecodingContainer $container, mixed $current): ?object
    {
        assert(is_null($current) || is_object($current));

        try {
            return $container->decodeObject($this->getClass(), $current);
        } catch (ValueNotFoundException) {
            return null;
        }
    }

    public function getAnnotationType(): string
    {
        return '\\' . $this->getClass();
    }

    public function getTypeScriptAnnotationType(): string
    {
        return 'any';
    }

    /**
     * Create field with object type for the given class.
     *
     * @param class-string $class
     */
    public static function createField(string $name, string $class): Field
    {
        return new Field($name, new static($class));
    }

    public function toJSONSchema(Context $context): array
    {
        return [];
    }

    /**
     * Create array field with object type for the given class.
     *
     * @param class-string $class
     */
    public static function createArrayField(string $name, string $class): ArrayField
    {
        return new ArrayField($name, new static($class));
    }
}
