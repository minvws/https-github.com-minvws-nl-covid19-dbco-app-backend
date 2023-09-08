<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\RiskLocation\RiskLocationCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class RiskLocationEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof RiskLocationCommon);

        $container->isLivingAtRiskLocation = $object->isLivingAtRiskLocation;

        if ($object->isLivingAtRiskLocation !== YesNoUnknown::yes()) {
            return;
        }

        $container->type = $object->type;
        $container->otherType = $object->otherType;
    }
}
