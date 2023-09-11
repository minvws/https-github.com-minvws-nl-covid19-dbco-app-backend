<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Types\DateTimeType;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

class DateTimeTypeDecoder implements DecodableDecorator
{
    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $format = $container->{'format'}->decodeStringIfPresent();

        return match ($format) {
            'date' => new DateTimeType(DateTimeType::FORMAT_DATE),
            'time' => new DateTimeType(DateTimeType::FORMAT_TIME),
            default => new DateTimeType()
        };
    }
}
