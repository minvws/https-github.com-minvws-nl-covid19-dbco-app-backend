<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Job\JobCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\JobSector;
use MinVWS\DBCO\Enum\Models\JobSectorGroup;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;
use function collect;

class JobEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof JobCommon);

        $container->wasAtJob = $object->wasAtJob;

        if ($object->wasAtJob !== YesNoUnknown::yes()) {
            return;
        }

        $container->sectors = $object->sectors;
        $container->particularities = $object->particularities;

        $sectors = collect($object->sectors);

        if ($sectors->contains('group.value', JobSectorGroup::care()->value)) {
            $container->professionCare = $object->professionCare;
        }

        if (!$sectors->contains('value', JobSector::andereBeroep()->value)) {
            return;
        }

        $container->closeContactAtJob = $object->closeContactAtJob;
        $container->professionOther = $object->professionOther;
        $container->otherProfession = $object->otherProfession;
    }
}
