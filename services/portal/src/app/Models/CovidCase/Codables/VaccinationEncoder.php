<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Vaccination\VaccinationCommon;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1UpTo1;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV1UpTo2;
use App\Models\Versions\CovidCase\Vaccination\VaccinationV3Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class VaccinationEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof VaccinationCommon);

        $container->isVaccinated = $object->isVaccinated;

        if ($object->isVaccinated === YesNoUnknown::yes()) {
            assert($object instanceof VaccinationV1UpTo2 || $object instanceof VaccinationV3Up);
            $container->vaccineInjections = $object->vaccineInjections;
        }

        if (!($object instanceof VaccinationV1UpTo1)) {
            return;
        }

        $container->hasReceivedInvite = $object->hasReceivedInvite;

        if ($object->hasReceivedInvite === YesNoUnknown::yes()) {
            $container->groups = $object->groups;
            $container->otherGroup = $object->otherGroup;
        }

        $container->hasCompletedVaccinationSeries = $object->hasCompletedVaccinationSeries;
    }
}
