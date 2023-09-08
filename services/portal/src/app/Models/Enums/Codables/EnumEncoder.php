<?php

declare(strict_types=1);

namespace App\Models\Enums\Codables;

use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;
use MinVWS\DBCO\Enum\Models\Enum;

class EnumEncoder implements StaticEncodableDecorator
{
    /**
     * @param object&Enum $object
     */
    public static function encode(object $object, EncodingContainer $container): void
    {
        $container->encodeString($object->label);
    }
}
