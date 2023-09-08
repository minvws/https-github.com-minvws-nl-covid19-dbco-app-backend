<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\AlternateContact\AlternateContactCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class AlternateContactEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof AlternateContactCommon);

        $container->hasAlternateContact = $object->hasAlternateContact;

        if ($object->hasAlternateContact !== YesNoUnknown::yes()) {
            return;
        }

        $container->firstname = $object->firstname;
        $container->lastname = $object->lastname;
        $container->gender = $object->gender;
        $container->relationship = $object->relationship;
        $container->explanation = $object->explanation;
    }
}
