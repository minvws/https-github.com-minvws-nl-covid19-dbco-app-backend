<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Symptoms\SymptomsCommon;
use App\Models\Versions\CovidCase\Symptoms\SymptomsV1UpTo1;
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

        if (!($object instanceof SymptomsV1UpTo1)) {
            return;
        }

        $container->wasSymptomaticAtTimeOfCall = $object->wasSymptomaticAtTimeOfCall;
        $container->stillHadSymptomsAt = $object->stillHadSymptomsAt;
    }
}
