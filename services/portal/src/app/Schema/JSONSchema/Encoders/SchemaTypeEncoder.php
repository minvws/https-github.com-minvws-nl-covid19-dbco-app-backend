<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\Fields\Field;
use App\Schema\JSONSchema\Misc\TypeDefs;
use App\Schema\Types\SchemaType;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function array_filter;
use function assert;

class SchemaTypeEncoder implements EncodableDecorator
{
    public function __construct(private readonly TypeDefs $typeDefs)
    {
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof SchemaType);

        $container->{'type'} = 'object';

        $fields = $value->getSchemaVersion()->getFields();
        if ($container->getContext()->getView() !== null) {
            $fields = array_filter($fields, static fn(Field $f) => $f->isInView($container->getContext()->getView()));
        }

        $container->{'properties'}->encodeArray($fields);

        if ($container->getParent() !== null && !$this->typeDefs->isEmpty()) {
            $container->{'$defs'} = $this->typeDefs->all();
        }
    }
}
