<?php

declare(strict_types=1);

namespace App\Models\Context\Codables;

use App\Models\Versions\Context\Contact\ContactCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class ContactEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof ContactCommon);

        $container->firstname = $object->firstname;
        $container->lastname = $object->lastname;
        $container->phone = $object->phone;
        $container->isInformed = $object->isInformed;
        $container->notificationConsent = $object->notificationConsent;
        $container->notificationNamedConsent = $object->notificationNamedConsent;
    }
}
