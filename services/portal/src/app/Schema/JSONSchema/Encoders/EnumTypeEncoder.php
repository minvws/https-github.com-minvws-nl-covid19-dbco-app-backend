<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\EnumCase;
use App\Schema\Types\EnumType;
use BackedEnum;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class EnumTypeEncoder implements EncodableDecorator
{
    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof EnumType);
        $container->type = 'string';
        $container->oneOf->encodeArray(
            $value->getEnum()->cases(),
            static function (EncodingContainer $caseContainer, EnumCase|BackedEnum $case): void {
                $caseContainer->title = $case->name;
                $caseContainer->const = $case->value;
            },
        );
    }
}
