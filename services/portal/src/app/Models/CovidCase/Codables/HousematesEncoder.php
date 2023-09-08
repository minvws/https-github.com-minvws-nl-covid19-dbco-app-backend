<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Housemates\HousematesCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class HousematesEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof HousematesCommon);

        $container->hasHouseMates = $object->hasHouseMates;

        if ($object->hasHouseMates !== YesNoUnknown::yes()) {
            return;
        }

        $container->hasOwnFacilities = $object->hasOwnFacilities;
        $container->hasOwnKitchen = $object->hasOwnKitchen;
        $container->hasOwnBedroom = $object->hasOwnBedroom;
        $container->hasOwnRestroom = $object->hasOwnRestroom;
        $container->canStrictlyIsolate = $object->canStrictlyIsolate;
    }
}
