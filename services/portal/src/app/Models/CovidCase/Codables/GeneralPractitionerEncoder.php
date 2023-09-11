<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\GeneralPractitioner\GeneralPractitionerCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class GeneralPractitionerEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof GeneralPractitionerCommon);

        $container->name = $object->name;
        $container->practiceName = $object->practiceName;
        $container->address = $object->address;
        $container->hasInfectionNotificationConsent = $object->hasInfectionNotificationConsent;
    }
}
