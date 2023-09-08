<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\RecentBirth\RecentBirthCommon;
use App\Models\Versions\CovidCase\RecentBirth\RecentBirthV1UpTo1;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class RecentBirthEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof RecentBirthCommon);

        if (!($object instanceof RecentBirthV1UpTo1)) {
            return;
        }

        $container->hasRecentlyGivenBirth = $object->hasRecentlyGivenBirth;

        if ($object->hasRecentlyGivenBirth === YesNoUnknown::yes()) {
            $container->birthDate = $object->birthDate;
        }
    }
}
