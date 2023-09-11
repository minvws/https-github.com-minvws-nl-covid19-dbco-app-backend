<?php

declare(strict_types=1);

namespace App\Schema\Types;

use App\Schema\Enum;
use App\Schema\EnumCase;
use App\Schema\Fields\ArrayField;
use App\Schema\Fields\Field;
use App\Schema\Generator\JSONSchema\Context;
use BackedEnum;
use Illuminate\Validation\Rule;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\ValueTypeMismatchException;

use function array_map;
use function assert;
use function gettype;
use function in_array;
use function is_int;
use function is_string;

class EnumType extends Type
{
    final public function __construct(private readonly Enum $enum)
    {
        parent::__construct();

        $values = array_map(static fn ($c) => $c->value, $this->enum->cases());

        $this->getValidationRules()
            ->addFatal('string')
            ->addFatal(static fn() => Rule::in($values));

        $this->setEncoder(fn (EncodingContainer $container, mixed $value) => $this->encodeValue($container, $value));
        $this->setDecoder(fn (DecodingContainer $container) => $this->decodeValue($container));
    }

    private function encodeValue(EncodingContainer $container, mixed $value): void
    {
        assert($value === null || $value instanceof EnumCase || $value instanceof BackedEnum);
        $container->encode($value->value ?? null);
    }

    private function decodeValue(DecodingContainer $container): null|EnumCase|BackedEnum
    {
        $value = $container->decode();
        if ($value === null) {
            return null;
        }

        if (!is_string($value) && !is_int($value)) {
            throw new ValueTypeMismatchException($container->getPath(), gettype($value), 'string|int');
        }

        return $this->enum->from($value);
    }

    public function getEnum(): Enum
    {
        return $this->enum;
    }

    public function isOfType(mixed $value): bool
    {
        return in_array($value, $this->enum->cases(), true);
    }

    public function getAnnotationType(): string
    {
        return EnumCase::class . '|' . BackedEnum::class;
    }

    public function getTypeScriptAnnotationType(): string
    {
        return 'any';
    }

    public static function createField(string $name, Enum $enum): Field
    {
        return new Field($name, new static($enum));
    }

    public static function createArrayField(string $name, Enum $enum): ArrayField
    {
        return new ArrayField($name, new static($enum));
    }

    public function toJSONSchema(Context $context): array
    {
        return [];
    }
}
