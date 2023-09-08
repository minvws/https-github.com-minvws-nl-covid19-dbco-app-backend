<?php

declare(strict_types=1);

namespace App\Schema\JSONSchema\Decoders;

use MinVWS\Codable\CodableException;
use MinVWS\Codable\DecodableDecorator;
use MinVWS\Codable\DecodingContainer;

use function in_array;

class TypeDecoder implements DecodableDecorator
{
    public function __construct(private readonly array $typeDecoders)
    {
    }

    public function decode(string $class, DecodingContainer $container, ?object $object = null): object
    {
        if ($container->contains('ref')) {
            $type = 'ref';
        } elseif ($container->contains('oneOf')) {
            $type = 'enum';
        } else {
            $type = $container->{'type'}->decodeStringIfPresent() ?? 'object';
        }

        if ($type === 'string' && $container->contains('format')) {
            $format = $container->{'format'}->decodeString();
            if (in_array($format, ['date-time', 'date', 'time'], true)) {
                $type = 'date-time';
            }
        }

        if (!isset($this->typeDecoders[$type])) {
            throw new CodableException('Unsupported type "' . $type . '"');
        }

        return $this->typeDecoders[$type]->decode($class, $container, $object);
    }
}
