<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\General\GeneralCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class GeneralEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof GeneralCommon);

        $container->reference = $object->reference;
        $container->firstname = $object->firstname;
        $container->lastname = $object->lastname;
        $container->email = $object->email;
        $container->phone = $object->phone;
        $container->dateOfLastExposure = $object->dateOfLastExposure;
        $container->category = $object->category;
        $container->isSource = $object->isSource;
        $container->label = $object->label;
        $container->context = $object->context;
        $container->relationship = $object->relationship;
        $container->otherRelationship = $object->otherRelationship;
        $container->closeContactDuringQuarantine = $object->closeContactDuringQuarantine;
        $container->nature = $object->nature;
        $container->deletedAt = $object->deletedAt;
    }
}
