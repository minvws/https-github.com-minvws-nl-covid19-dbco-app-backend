<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Types\ArrayType;
use App\Schema\Types\Type;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

use function assert;

class ArrayTypeDecoder implements DecodableDecorator
{
    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $itemType = $container->items->decodeObject(Type::class);
        assert($itemType instanceof Type);
        return new ArrayType($itemType);
    }
}
