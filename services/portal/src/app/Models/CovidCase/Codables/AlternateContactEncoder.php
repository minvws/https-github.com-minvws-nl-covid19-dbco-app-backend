<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\AlternateContact\AlternateContactCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

class AlternateContactEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        Assert::isInstanceOf($object, AlternateContactCommon::class);

        $container->hasAlternateContact = $object->hasAlternateContact;

        if ($object->hasAlternateContact !== YesNoUnknown::yes()) {
            return;
        }

        $container->firstname = $object->firstname;
        $container->lastname = $object->lastname;
        $container->gender = $object->gender;
        $container->relationship = $object->relationship;
        $container->phone = $object->phone;
        $container->email = $object->email;
        $container->isDefaultContact = $object->isDefaultContact;
    }
}
