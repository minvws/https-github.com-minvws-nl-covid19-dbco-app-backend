<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Test\TestCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;

use function assert;

class TestEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof TestCommon);

        $container->isTested = $object->isTested;

        if ($object->isTested !== YesNoUnknown::yes()) {
            return;
        }

        $container->testResult = $object->testResult;
        $container->dateOfTest = $object->dateOfTest;
    }
}
