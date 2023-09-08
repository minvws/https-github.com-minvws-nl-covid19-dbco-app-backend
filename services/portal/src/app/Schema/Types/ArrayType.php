<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Generator\JSONSchema\Context;
use InvalidArgumentException;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;

use function assert;
use function count;
use function get_class;
use function is_a;
use function is_array;
use function is_iterable;
use function is_null;

/**
 * Normally there is no need to use this type directly. Use ArrayField instead.
 */
class ArrayType extends Type
{
    private Type $elementType;

    public function __construct(Type $elementType)
    {
        parent::__construct();

        $this->elementType = $elementType;

        $this->getValidationRules()
            ->addFatal('array')
            ->addChild($elementType->getValidationRules(), '*');
    }

    public function getElementType(): Type
    {
        return $this->elementType;
    }

    /**
     * The element type.
     *
     * Guarantees that the returned type is of the given expected type, otherwise throws an exception.
     * Useful for method chaining using generics.
     *
     * @template E of Type
     *
     * @param class-string<E> $expectedType
     *
     * @return E
     */
    public function getExpectedElementType(string $expectedType): Type
    {
        if (!is_a($this->elementType, $expectedType)) {
            throw new InvalidArgumentException(
                "Expected type \"$expectedType\" does not match field type \"" . get_class($this->elementType) . "\"!",
            );
        }

        return $this->elementType;
    }

    public function isOfType(mixed $value): bool
    {
        return is_array($value);
    }

    public function encode(EncodingContainer $container, mixed $value): void
    {
        assert(is_null($value) || is_iterable($value));

        $container->encodeArray(
            $value,
            fn (EncodingContainer $elementContainer, $elementValue) =>
            $this->getElementType()->encode($elementContainer, $elementValue)
        );
    }

    /**
     * @inheritDoc
     */
    public function decode(DecodingContainer $container, mixed $current): ?array
    {
        return $container->decodeArrayIfPresent(
            fn (DecodingContainer $elementContainer) =>
                $this->getElementType()->decode($elementContainer, null)
        );
    }

    public function getAnnotationType(): string
    {
        return 'array<' . $this->getElementType()->getAnnotationType() . '>';
    }

    public function getTypeScriptAnnotationType(): string
    {
        return $this->getElementType()->getTypeScriptAnnotationType() . '[]';
    }

    public function toJSONSchema(Context $context): array
    {
        return ['type' => 'array', 'items' => $this->elementType->toJSONSchema($context)];
    }

    public function valuesEqual(mixed $value1, mixed $value2): bool
    {
        if ($value1 === null && $value2 === null) {
            return true;
        }

        if (!is_array($value1) || !$this->isOfType($value1)) {
            return false;
        }

        if (!is_array($value2) || !$this->isOfType($value2)) {
            return false;
        }

        if (count($value1) !== count($value2)) {
            return false;
        }

        foreach ($value1 as $i => $el1) {
            $el2 = $value2[$i] ?? null;
            if (!$this->getElementType()->valuesEqual($el1, $el2)) {
                return false;
            }
        }

        return true;
    }
}
