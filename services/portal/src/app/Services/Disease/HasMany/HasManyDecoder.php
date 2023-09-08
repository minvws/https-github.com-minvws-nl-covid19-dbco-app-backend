<?php

declare(strict_types=1);

namespace App\Services\Disease\HasMany;

use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

class HasManyDecoder implements DecodableDecorator
{
    public function __construct(private readonly array $schemas)
    {
    }

    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $name = $container->decodeStringKey();
        $schema = $this->schemas[$name];
        $listProperties = $container->{'listProperties'}->decodeArray();
        return new HasManyType($schema, $listProperties);
    }
}
