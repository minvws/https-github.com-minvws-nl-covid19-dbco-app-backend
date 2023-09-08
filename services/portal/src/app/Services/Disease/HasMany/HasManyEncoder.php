<?php

declare(strict_types=1);

namespace App\Services\Disease\HasMany;

use App\Schema\Types\SchemaType;
use MinVWS\Codable\EncodableDecorator;
use MinVWS\Codable\EncodingContainer;

use function assert;

class HasManyEncoder implements EncodableDecorator
{
    private function encodeSchema(HasManyType $type, EncodingContainer $container): void
    {
        $container->getContext()->setView(HasManyType::VIEW_LIST);
        $container->encodeObject(new SchemaType($type->schema));
    }

    public function encode(object $value, EncodingContainer $container): void
    {
        assert($value instanceof HasManyType);
        $container->{'type'} = 'object';
        $container->{'properties'}->{'data'}->{'type'} = 'array';
        $container->{'properties'}->{'data'}->{'items'}->{'type'} = 'object';
        $this->encodeSchema($value, $container->{'properties'}->{'data'}->{'items'}->{'properties'}->{'data'});
        $container->{'properties'}->{'links'}->{'type'} = 'object';
        $container->{'properties'}->{'links'}->{'additionalProperties'}->{'type'} = 'object';
        $container->{'properties'}->{'links'}->{'additionalProperties'}->{'properties'}->{'href'}->{'type'} = 'string';
        $container->{'properties'}->{'links'}->{'additionalProperties'}->{'properties'}->{'method'}->{'type'} = 'string';
    }
}
