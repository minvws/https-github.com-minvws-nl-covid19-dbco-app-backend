<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Job\JobCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class JobEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof JobCommon);

        $container->worksInAviation = $object->worksInAviation;
        $container->worksInHealthCare = $object->worksInHealthCare;

        if ($object->worksInHealthCare === YesNoUnknown::yes()) {
            $container->healthCareFunction = $object->healthCareFunction;
        }
    }
}
