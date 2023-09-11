<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\Types\BoolType;
use App\Schema\Types\FloatType;
use App\Schema\Types\IntType;
use App\Schema\Types\StringType;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

class ScalarTypeEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        $container->{'type'} = match ($value::class) {
            StringType::class => 'string',
            IntType::class, FloatType::class => 'number',
            BoolType::class => 'boolean',
            default => throw new CodableException('Unsupported scalar type "' . $value::class . '"')
        };
    }
}
