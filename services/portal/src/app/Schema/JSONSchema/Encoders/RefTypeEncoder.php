<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\JSONSchema\Misc\RefType;
use App\Schema\JSONSchema\Misc\TypeDefs;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class RefTypeEncoder implements EncodableDecorator
{
    private const PREFIX = '#/$defs/';

    public function __construct(private readonly TypeDefs $typeDefs)
    {
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof RefType);
        $this->typeDefs->register($value->defName, $value->getType());
        $container->{'$ref'} = self::PREFIX . $value->defName;
    }
}
