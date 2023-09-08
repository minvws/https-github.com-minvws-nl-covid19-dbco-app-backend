<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\General\GeneralCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class GeneralEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof GeneralCommon);

        $container->source = $object->source;
        $container->reference = $object->reference;
        $container->hpzoneNumber = $object->hpzoneNumber;
        $container->createdAt = $object->createdAt;
        $container->deletedAt = $object->deletedAt;
        $container->organisation = $object->organisation?->name;
    }
}
