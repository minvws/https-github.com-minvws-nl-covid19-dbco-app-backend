<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Types\BoolType;
use App\Schema\Types\FloatType;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

class ScalarTypeDecoder implements DecodableDecorator
{
    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $type = $container->type->decodeString();

        return match ($type) {
            'string' => new StringType(),
            'integer' => new IntType(),
            'number' => new FloatType(),
            'boolean' => new BoolType(),
            default => throw new CodableException('Unsupported scalar type "' . $type . '"')
        };
    }
}
