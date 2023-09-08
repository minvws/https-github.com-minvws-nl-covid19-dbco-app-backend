<?php

declare(strict_types=1);

namespace App\Models\Context\Codables;

use App\Models\Versions\Context\Moment\MomentCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;
use function sprintf;

class MomentEncoder implements StaticEncodableDecorator
{
    public static function encode(?object $object, EncodingContainer $container): void
    {
        if ($object === null) {
            return;
        }

        assert($object instanceof MomentCommon);

        $day = $object->day?->format('Y-m-d');

        if ($object->startTime === null && $object->endTime === null) {
            $container->encodeString($day);
            return;
        }

        $container->encodeString(sprintf(
            '%s (%s - %s)',
            $day,
            $object->startTime ?? '?',
            $object->endTime ?? '?',
        ));
    }
}
