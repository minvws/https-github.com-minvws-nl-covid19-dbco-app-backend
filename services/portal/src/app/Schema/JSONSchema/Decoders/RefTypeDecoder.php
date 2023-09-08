<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use App\Schema\JSONSchema\Misc\RefType;
use App\Schema\JSONSchema\Misc\TypeDefs;
use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

use function str_starts_with;
use function strlen;
use function substr;

class RefTypeDecoder implements DecodableDecorator
{
    private const PREFIX = '#/$defs/';

    public function __construct(private readonly TypeDefs $typeDefs)
    {
    }

    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        $ref = $container->{'$ref'}->decodeString();

        if (!str_starts_with($ref, self::PREFIX)) {
            throw new CodableException('Unsupported property ref: ' . $ref);
        }

        $name = substr($ref, strlen(self::PREFIX));

        return new RefType($name, $this->typeDefs);
    }
}
