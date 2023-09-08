<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Immunity\ImmunityCommon;
use App\Models\Versions\Task\Immunity\ImmunityV1UpTo1;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class ImmunityEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof ImmunityCommon);

        if (!($object instanceof ImmunityV1UpTo1)) {
            return;
        }

        $container->isImmune = $object->isImmune;

        if ($object->isImmune === YesNoUnknown::yes()) {
            $container->remarks = $object->remarks;
        }
    }
}
