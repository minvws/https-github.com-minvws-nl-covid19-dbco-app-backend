<?php

namespace MinVWS\Codable;

/**
 * External decodable implementation for class.
 *
 * @package MinVWS\Codable
 */
interface DecodableDecorator
{
    /**
     * Decode to the given class.
     *
     * @template T
     *
     * @param class-string<T>   $class     Target class.
     * @param DecodingContainer $container Decoding container.
     * @param T|null            $object    Decode into the given object.
     *
     * @return T
     *
     * @throws CodableException
     */
    public function decode(string $class, DecodingContainer $container, ?object $object = null): object;
}
