<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Contacts\ContactsCommon;
use App\Models\Versions\CovidCase\Contacts\ContactsV1;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class ContactsEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof ContactsCommon);

        $container->shareNameWithContacts = $object->shareNameWithContacts;

        if ($object instanceof ContactsV1) {
            $container->estimatedCategory3Contacts = $object->estimatedCategory3Contacts;
        }
    }
}
