<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Encoders;

use App\Schema\Fields\Field;
use App\Schema\Purpose\PurposeSpecification;
use App\Schema\Validation\ValidationRules;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class FieldEncoder implements EncodableDecorator
{
    private function encodePurposeSpecification(EncodingContainer $container, PurposeSpecification $spec): void
    {
    }

    private function encodeValidationRules(EncodingContainer $container, ValidationRules $rules): void
    {
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof Field);
        $container->encodeObject($value->getType());
        $this->encodeValidationRules($container, $value->getValidationRules());
        $this->encodePurposeSpecification($container, $value->getPurposeSpecification());
    }
}
