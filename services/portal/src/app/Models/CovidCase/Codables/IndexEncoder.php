<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Index\IndexCommon;
use App\Models\Versions\CovidCase\Index\IndexV1UpTo1;
use App\Models\Versions\CovidCase\Index\IndexV2Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class IndexEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof IndexCommon);

        $container->firstname = $object->firstname;
        $container->initials = $object->initials;
        $container->lastname = $object->lastname;
        $container->dateOfBirth = $object->dateOfBirth;
        $container->gender = $object->gender;
        $container->address = $object->address;
        $container->bsnCensored = $object->bsnCensored;
        $container->bsnLetters = $object->bsnLetters;
        $container->bsnNotes = $object->bsnNotes;

        if ($object instanceof IndexV1UpTo1 || $object instanceof IndexV2Up) {
            $container->hasNoBsnOrAddress = $object->hasNoBsnOrAddress;
        }
    }
}
