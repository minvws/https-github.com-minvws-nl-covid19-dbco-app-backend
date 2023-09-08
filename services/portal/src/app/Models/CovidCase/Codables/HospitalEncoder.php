<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Hospital\HospitalCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class HospitalEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof HospitalCommon);

        $container->isAdmitted = $object->isAdmitted;

        if ($object->isAdmitted !== YesNoUnknown::yes()) {
            return;
        }

        $container->name = $object->name;
        $container->location = $object->location;
        $container->admittedAt = $object->admittedAt;
        $container->releasedAt = $object->releasedAt;
        $container->reason = $object->reason;
        $container->practitioner = $object->practitioner;
        $container->practitionerPhone = $object->practitionerPhone;
        $container->isInICU = $object->isInICU;

        if ($object->isInICU === YesNoUnknown::yes()) {
            $container->admittedInICUAt = $object->admittedInICUAt;
        }
    }
}
