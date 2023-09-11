<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\GroupTransport\GroupTransportCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class GroupTransportEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof GroupTransportCommon);

        $container->withReservedSeats = $object->withReservedSeats;
    }
}
