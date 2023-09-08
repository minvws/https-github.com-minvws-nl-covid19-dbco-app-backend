<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\SourceEnvironments\SourceEnvironmentsCommon;
use App\Models\Versions\CovidCase\SourceEnvironments\SourceEnvironmentsV1UpTo1;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class SourceEnvironmentsEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof SourceEnvironmentsCommon);

        if (!($object instanceof SourceEnvironmentsV1UpTo1)) {
            return;
        }

        $container->hasLikelySourceEnvironments = $object->hasLikelySourceEnvironments;

        if ($object->hasLikelySourceEnvironments === YesNoUnknown::yes()) {
            $container->likelySourceEnvironments = $object->likelySourceEnvironments;
        }
    }
}
