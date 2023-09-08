<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema;

use App\Schema\Entity;
use App\Schema\Fields\Field;
use App\Schema\JSONSchema\Decoders\ArrayTypeDecoder;
use App\Schema\JSONSchema\Decoders\DateTimeTypeDecoder;
use App\Schema\JSONSchema\Decoders\EnumTypeDecoder;
use App\Schema\JSONSchema\Decoders\FieldDecoder;
use App\Schema\JSONSchema\Decoders\RefTypeDecoder;
use App\Schema\JSONSchema\Decoders\ScalarTypeDecoder;
use App\Schema\JSONSchema\Decoders\SchemaFactory;
use App\Schema\JSONSchema\Decoders\SchemaTypeDecoder;
use App\Schema\JSONSchema\Decoders\TypeDecoder;
use App\Schema\JSONSchema\Misc\TypeDefs;
use App\Schema\Schema;
use App\Schema\Types\Type;
use Closure;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;
use MinVWS\Codable\JSONDecoder;

use function assert;
use function call_user_func;

class JSONSchemaDecoder
{
    private SchemaFactory $schemaFactory;
    private array $typeDecoders = [];

    public function __construct()
    {
        $this->setSchemaFactory(static fn () => new Schema(Entity::class));
    }

    /**
     * @param SchemaFactory|(Closure(DecodingContainer): Schema) $factory
     */
    public function setSchemaFactory(SchemaFactory|Closure $factory): void
    {
        if ($factory instanceof SchemaFactory) {
            $this->schemaFactory = $factory;
            return;
        }

        $this->schemaFactory = new class ($factory) implements SchemaFactory {
            /**
             * @param (Closure(DecodingContainer $container): Schema) $factory
             */
            public function __construct(private readonly Closure $factory)
            {
            }

            public function createSchema(DecodingContainer $container): Schema
            {
                $schema = call_user_func($this->factory, $container);
                assert($schema instanceof Schema);
                return $schema;
            }
        };
    }

    public function registerTypeDecoder(string $typeName, DecodableDecorator $decoder): void
    {
        $this->typeDecoders[$typeName] = $decoder;
    }

    public function decode(string $json): Type
    {
        $typeDefs = new TypeDefs();

        $decoder = new JSONDecoder();

        $typeDecoders = $this->typeDecoders;
        $typeDecoders['ref'] ??= new RefTypeDecoder($typeDefs);
        $typeDecoders['array'] ??= new ArrayTypeDecoder();
        $typeDecoders['object'] ??= new SchemaTypeDecoder($this->schemaFactory);
        $typeDecoders['enum'] ??= new EnumTypeDecoder();
        $scalarTypeDecoder = new ScalarTypeDecoder();
        $typeDecoders['string'] ??= $scalarTypeDecoder;
        $typeDecoders['integer'] ??= $scalarTypeDecoder;
        $typeDecoders['number'] ??= $scalarTypeDecoder;
        $typeDecoders['boolean'] ??= $scalarTypeDecoder;
        $typeDecoders['date-time'] ??= new DateTimeTypeDecoder();

        $decoder->getContext()->registerDecorator(Type::class, new TypeDecoder($typeDecoders));
        $decoder->getContext()->registerDecorator(Field::class, new FieldDecoder());

        $container = $decoder->decode($json);

        $defs = $container->{'$defs'}->decodeArrayIfPresent(Type::class) ?? [];
        $typeDefs->registerAll($defs);

        return $container->decodeObject(Type::class);
    }
}
