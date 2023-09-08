<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Circumstances\CircumstancesCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class CircumstancesEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof CircumstancesCommon);

        $container->wasUsingPPE = $object->wasUsingPPE;

        if ($object->wasUsingPPE !== YesNoUnknown::yes()) {
            return;
        }

        $container->usedPersonalProtectiveEquipment = $object->usedPersonalProtectiveEquipment;
        $container->wasUsingPPE = $object->wasUsingPPE;
        $container->ppeMedicallyCompetent = $object->ppeMedicallyCompetent;
        $container->ppeReplaceFrequency = $object->ppeReplaceFrequency;
    }
}
