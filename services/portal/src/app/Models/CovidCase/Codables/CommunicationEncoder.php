<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\Communication\CommunicationCommon;
use App\Models\Versions\CovidCase\Communication\CommunicationV1UpTo1;
use App\Models\Versions\CovidCase\Communication\CommunicationV2UpTo3;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

final class CommunicationEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof CommunicationCommon);

        $container->particularities = $object->particularities;

        if ($object instanceof CommunicationV1UpTo1 || $object instanceof CommunicationV2UpTo3) {
            $container->isolationAdviceGiven = $object->isolationAdviceGiven;
        }
    }
}
