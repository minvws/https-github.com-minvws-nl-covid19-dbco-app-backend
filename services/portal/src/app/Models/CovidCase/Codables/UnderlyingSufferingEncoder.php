<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingCommon;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingV1UpTo1;
use App\Models\Versions\CovidCase\UnderlyingSuffering\UnderlyingSufferingV2Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class UnderlyingSufferingEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof UnderlyingSufferingCommon);

        $container->hasUnderlyingSufferingOrMedication = $object->hasUnderlyingSufferingOrMedication;
        $container->hasUnderlyingSuffering = $object->hasUnderlyingSuffering;

        if ($object->hasUnderlyingSuffering !== YesNoUnknown::yes()) {
            return;
        }

        if ($object instanceof UnderlyingSufferingV1UpTo1 || $object instanceof UnderlyingSufferingV2Up) {
            $container->items = $object->items;
        }

        $container->otherItems = $object->otherItems;
        $container->remarks = $object->remarks;
    }
}
