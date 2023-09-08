<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\Types\ArrayType;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class ArrayTypeEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof ArrayType);
        $container->{'type'} = 'array';
        $container->{'items'} = $value->getElementType();
    }
}
