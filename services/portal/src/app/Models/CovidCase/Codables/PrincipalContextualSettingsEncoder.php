<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\PrincipalContextualSettings\PrincipalContextualSettingsCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class PrincipalContextualSettingsEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof PrincipalContextualSettingsCommon);

        $container->hasPrincipalContextualSettings = $object->hasPrincipalContextualSettings;

        if (!$object->hasPrincipalContextualSettings) {
            return;
        }

        $container->items = $object->items;
        $container->otherItems = $object->otherItems;
    }
}
