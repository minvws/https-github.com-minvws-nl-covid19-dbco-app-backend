<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\Enum;
use App\Schema\EnumCase;
use App\Schema\Types\EnumType;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

class EnumTypeDecoder implements DecodableDecorator
{
    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $cases = $container->oneOf->decodeArray(static function (DecodingContainer $caseContainer) {
            $value = $caseContainer->const->decodeString();
            $name = $caseContainer->title->decodeStringIfPresent();
            return EnumCase::create($value, $name);
        });

        $enum = Enum::forCases($cases);
        return new EnumType($enum);
    }
}
