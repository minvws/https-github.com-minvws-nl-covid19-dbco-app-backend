<?php

declare(strict_types=1);

namespace App\Models\CovidCase\Codables;

use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingCommon;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV1UpTo1;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV1UpTo2;
use App\Models\Versions\CovidCase\ExtensiveContactTracing\ExtensiveContactTracingV2Up;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

final class ExtensiveContactTracingEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof ExtensiveContactTracingCommon);

        if ($object instanceof ExtensiveContactTracingV1UpTo1 || $object instanceof ExtensiveContactTracingV2Up) {
            $container->receivesExtensiveContactTracing = $object->receivesExtensiveContactTracing;
        }

        if (!$object instanceof ExtensiveContactTracingV1UpTo2) {
            return;
        }

        $container->reasons = $object->reasons;
        $container->notes = $object->notes;
    }
}
