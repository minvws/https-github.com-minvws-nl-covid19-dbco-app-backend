<?php

declare(strict_types=1);

namespace App\Models\Task\Codables;

use App\Models\Versions\Task\Inform\InformCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class InformEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof InformCommon);

        $container->status = $object->status;
        $container->informedBy = $object->informedBy;
        $container->shareIndexNameWithContact = $object->shareIndexNameWithContact;
        $container->informTarget = $object->informTarget;
    }
}
