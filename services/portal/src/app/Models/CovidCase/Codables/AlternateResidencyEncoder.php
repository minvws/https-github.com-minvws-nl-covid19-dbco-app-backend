<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\AlternateResidency\AlternateResidencyCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class AlternateResidencyEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof AlternateResidencyCommon);

        $container->hasAlternateResidency = $object->hasAlternateResidency;

        if ($object->hasAlternateResidency === YesNoUnknown::yes()) {
            $container->address = $object->address;
        }
    }
}
