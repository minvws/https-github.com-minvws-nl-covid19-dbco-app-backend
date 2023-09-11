<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Deceased\DeceasedCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class DeceasedEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof DeceasedCommon);

        $container->isDeceased = $object->isDeceased;

        if ($object->isDeceased !== YesNoUnknown::yes()) {
            return;
        }

        $container->deceasedAt = $object->deceasedAt;
        $container->cause = $object->cause;
    }
}
