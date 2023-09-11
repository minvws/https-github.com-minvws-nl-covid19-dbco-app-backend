<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Vaccination\VaccinationCommon;
use App\Models\Versions\Task\Vaccination\VaccinationV1UpTo1;
use App\Models\Versions\Task\Vaccination\VaccinationV1UpTo2;
use App\Models\Versions\Task\Vaccination\VaccinationV3Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class VaccinationEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof VaccinationCommon);

        if ($object instanceof VaccinationV1UpTo1) {
            $container->hasCompletedVaccinationSeries = $object->hasCompletedVaccinationSeries;
        }

        if ($object instanceof VaccinationV1UpTo2 || $object instanceof VaccinationV3Up) {
            $container->vaccineInjections = $object->vaccineInjections;
        }
    }
}
