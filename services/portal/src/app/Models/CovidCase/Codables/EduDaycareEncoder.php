<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\CovidCase\EduDaycare;
use App\Models\Versions\CovidCase\EduDaycare\EduDaycareV1UpTo1;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use Webmozart\Assert\Assert;

class EduDaycareEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        Assert::isInstanceOf($object, EduDaycare::class);

        if (!$object instanceof EduDaycareV1UpTo1) {
            return;
        }

        $container->isStudent = $object->isStudent;

        if ($object->isStudent === YesNoUnknown::yes()) {
            $container->type = $object->type;
        }
    }
}
