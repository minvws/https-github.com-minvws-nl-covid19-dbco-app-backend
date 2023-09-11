<?php

declare(strict_types=1);

namespace App\Schema\Generator\JSONSchema\Diff\Schema;

use MinVWS\Codable\Decodable;
use MinVWS\Codable\DecodingContainer;

class SubPurpose implements Decodable
{
    public function __construct(public readonly string $identifier, public readonly string $description)
    {
    }

    public static function decode(DecodingContainer $container, ?object $object = null): self
    {
        $identifier = $container->{'identifier'}->decodeString();
        $description = $container->{'description'}->decodeString();
        return new self($identifier, $description);
    }
}
