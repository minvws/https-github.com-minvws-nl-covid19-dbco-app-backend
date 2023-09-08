<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\Types\DateTimeType;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class DateTimeTypeEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof DateTimeType);
        $container->{'type'} = 'string';
        $container->{'format'} = match ($value->getFormat()) {
            DateTimeType::FORMAT_DATE => 'date',
            DateTimeType::FORMAT_TIME => 'time',
            default => 'date-time'
        };
    }
}
