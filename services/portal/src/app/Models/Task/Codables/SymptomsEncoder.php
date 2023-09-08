<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Symptoms\SymptomsCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class SymptomsEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof SymptomsCommon);

        $container->hasSymptoms = $object->hasSymptoms;

        if ($object->hasSymptoms !== YesNoUnknown::yes()) {
            return;
        }

        $container->symptoms = $object->symptoms;
        $container->otherSymptoms = $object->otherSymptoms;
    }
}
