<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Pregnancy\PregnancyCommon;
use App\Models\Versions\CovidCase\Pregnancy\PregnancyV1;
use App\Models\Versions\CovidCase\Pregnancy\PregnancyV2;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class PregnancyEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof PregnancyCommon);

        $container->isPregnant = $object->isPregnant;

        if ($object->isPregnant !== YesNoUnknown::yes()) {
            return;
        }

        if ($object instanceof PregnancyV1) {
            $container->dueDate = $object->dueDate;
        }

        if ($object instanceof PregnancyV2) {
            $container->remarks = $object->remarks;
        }
    }
}
