<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Fields\Field;
use App\Schema\Types\SchemaType;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

use function assert;

class SchemaTypeDecoder implements DecodableDecorator
{
    public function __construct(private readonly SchemaFactory $schemaFactory)
    {
    }

    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $schema = $this->schemaFactory->createSchema($container);

        $fields = $container->{'properties'}->decodeArrayIfPresent(static function (DecodingContainer $fieldContainer) {
            return $fieldContainer->decodeObject(Field::class, null, true);
        }) ?? [];

        foreach ($fields as $field) {
            assert($field instanceof Field);
            $schema->add($field);
        }

        return new SchemaType($schema);
    }
}
