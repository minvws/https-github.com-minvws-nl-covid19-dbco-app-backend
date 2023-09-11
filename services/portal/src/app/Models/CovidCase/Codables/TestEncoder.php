<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Test\TestCommon;
use App\Models\Versions\CovidCase\Test\TestV1UpTo3;
use App\Models\Versions\CovidCase\Test\TestV3Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\InfectionIndicator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class TestEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof TestCommon);

        $container->dateOfSymptomOnset = $object->dateOfSymptomOnset;
        $container->isSymptomOnsetEstimated = $object->isSymptomOnsetEstimated ?? false;
        $container->dateOfTest = $object->dateOfTest;
        $container->dateOfResult = $object->dateOfResult;
        $container->dateOfInfectiousnessStart = $object->dateOfInfectiousnessStart;

        if ($object instanceof TestV3Up) {
            $container->reasons = $object->reasons;
        }

        $container->otherReason = $object->otherReason;
        $container->infectionIndicator = $object->infectionIndicator;

        if ($object->infectionIndicator === InfectionIndicator::selfTest()) {
            $container->selfTestIndicator = $object->selfTestIndicator;
        }

        if ($object->infectionIndicator === InfectionIndicator::labTest()) {
            $container->labTestIndicator = $object->labTestIndicator;
            $container->otherLabTestIndicator = $object->otherLabTestIndicator;
        }

        $container->monsterNumber = $object->monsterNumber;

        $container->isReinfection = $object->isReinfection;

        if ($object->isReinfection !== YesNoUnknown::yes()) {
            return;
        }

        $container->previousInfectionDateOfSymptom = $object->previousInfectionDateOfSymptom;
        $container->previousInfectionSymptomFree = $object->previousInfectionSymptomFree;
        $container->previousInfectionProven = $object->previousInfectionProven;
        $container->contactOfConfirmedInfection = $object->contactOfConfirmedInfection;
        $container->previousInfectionReported = $object->previousInfectionReported;

        if ($object instanceof TestV1UpTo3) {
            $container->previousInfectionHpzoneNumber = $object->previousInfectionHpzoneNumber;
        } else {
            $container->previousInfectionCaseNumber = $object->previousInfectionCaseNumber;
        }
    }
}
