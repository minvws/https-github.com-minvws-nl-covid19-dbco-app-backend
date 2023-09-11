<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\PersonalDetails\PersonalDetailsCommon;
use App\Models\Versions\Task\PersonalDetails\PersonalDetailsV2Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class PersonalDetailsEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof PersonalDetailsCommon);

        $container->dateOfBirth = $object->dateOfBirth;
        $container->gender = $object->gender;
        $container->bsnLetters = $object->bsnLetters;
        $container->bsnCensored = $object->bsnCensored;
        $container->bsnNotes = $object->bsnNotes;
        $container->address = $object->address;

        if ($object instanceof PersonalDetailsV2Up) {
            $container->hasNoBsnOrAddress = $object->hasNoBsnOrAddress;
        }
    }
}
