<?php

declare(strict_types=1);

namespace App\Models\Context\Codables;

use App\Models\Context\Moment;
use App\Models\Versions\Context\General\GeneralCommon;
use MinVWS\Codable\EncodingContainer;
use MinVWS\Codable\StaticEncodableDecorator;

use function assert;

class GeneralEncoder implements StaticEncodableDecorator
{
    public static function encode(object $object, EncodingContainer $container): void
    {
        assert($object instanceof GeneralCommon);

        $container->getContext()->registerDecorator(Moment::class, MomentEncoder::class);

        $container->label = $object->label;
        $container->relationship = $object->relationship;
        $container->otherRelationship = $object->otherRelationship;
        $container->isSource = $object->isSource;
        $container->moments = $object->moments;
    }
}
