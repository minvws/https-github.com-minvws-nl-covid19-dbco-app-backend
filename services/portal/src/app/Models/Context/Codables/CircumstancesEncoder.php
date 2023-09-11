<?php

declare(strict_types=1);

namespace App\Models\Context\Codables;

use App\Models\Versions\Context\Circumstances\CircumstancesCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class CircumstancesEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof CircumstancesCommon);

        $container->isUsingPPE = $object->isUsingPPE;

        if ($object->isUsingPPE === YesNoUnknown::yes()) {
            $container->usedPersonalProtectiveEquipment = $object->usedPersonalProtectiveEquipment;
            $container->ppeType = $object->ppeType;
            $container->ppeReplaceFrequency = $object->ppeReplaceFrequency;
        }

        $container->covidMeasures = $object->covidMeasures;
        $container->otherCovidMeasures = $object->otherCovidMeasures;

        $container->causeForConcern = $object->causeForConcern;

        if ($object->causeForConcern === YesNoUnknown::yes()) {
            $container->causeForConcernRemark = $object->causeForConcernRemark;
        }

        $container->sharedTransportation = $object->sharedTransportation;
    }
}
