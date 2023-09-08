<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema;

use App\Schema\Fields\Field;
use App\Schema\JSONSchema\Encoders\ArrayTypeEncoder;
use App\Schema\JSONSchema\Encoders\DateTimeTypeEncoder;
use App\Schema\JSONSchema\Encoders\EnumTypeEncoder;
use App\Schema\JSONSchema\Encoders\FieldEncoder;
use App\Schema\JSONSchema\Encoders\RefTypeEncoder;
use App\Schema\JSONSchema\Encoders\ScalarTypeEncoder;
use App\Schema\JSONSchema\Encoders\SchemaTypeEncoder;
use App\Schema\JSONSchema\Misc\RefType;
use App\Schema\JSONSchema\Misc\TypeDefs;
use App\Schema\Schema;
use App\Schema\Types\ArrayType;
use App\Schema\Types\BoolType;
use App\Schema\Types\DateTimeType;
use App\Schema\Types\EnumType;
use App\Schema\Types\FloatType;
use App\Schema\Types\IntType;
use App\Schema\Types\SchemaType;
use App\Schema\Types\StringType;
use JsonException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\Encoder;

use function assert;
use function is_object;
use function json_encode;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;

class JSONSchemaEncoder
{
    /** @var array<class-string, EncodableDecorator> */
    private array $typeEncoders = [];

    /**
     * @param class-string $className
     */
    public function registerTypeEncoder(string $className, EncodableDecorator $encoder): void
    {
        $this->typeEncoders[$className] = $encoder;
    }

    public function encode(Schema $schema): object
    {
        $typeDefs = new TypeDefs();

        $encoder = new Encoder();

        $encoder->getContext()->registerDecorator(Field::class, new FieldEncoder());
        $encoder->getContext()->registerDecorator(RefType::class, $this->typeEncoders[RefType::class] ?? new RefTypeEncoder($typeDefs));
        $encoder->getContext()->registerDecorator(
            SchemaType::class,
            $this->typeEncoders[SchemaType::class] ?? new SchemaTypeEncoder($typeDefs),
        );
        $encoder->getContext()->registerDecorator(EnumType::class, $this->typeEncoders[EnumType::class] ?? new EnumTypeEncoder());
        $encoder->getContext()->registerDecorator(ArrayType::class, $this->typeEncoders[ArrayType::class] ?? new ArrayTypeEncoder());
        $scalarTypeEncoder = new ScalarTypeEncoder();
        $encoder->getContext()->registerDecorator(StringType::class, $this->typeEncoders[StringType::class] ?? $scalarTypeEncoder);
        $encoder->getContext()->registerDecorator(IntType::class, $this->typeEncoders[IntType::class] ?? $scalarTypeEncoder);
        $encoder->getContext()->registerDecorator(FloatType::class, $this->typeEncoders[FloatType::class] ?? $scalarTypeEncoder);
        $encoder->getContext()->registerDecorator(BoolType::class, $this->typeEncoders[BoolType::class] ?? $scalarTypeEncoder);
        $encoder->getContext()->registerDecorator(
            DateTimeType::class,
            $this->typeEncoders[DateTimeType::class] ?? new DateTimeTypeEncoder(),
        );

        foreach ($this->typeEncoders as $className => $decorator) {
            $encoder->getContext()->registerDecorator($className, $decorator);
        }

        $result = $encoder->encode(new SchemaType($schema));
        assert(is_object($result));
        return $result;
    }

    /**
     * @param int<1, 512> $depth
     *
     * @throws JsonException
     */
    public function encodeJson(Schema $schema, int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES, int $depth = 512): string
    {
        $data = $this->encode($schema);
        return json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
    }
}
