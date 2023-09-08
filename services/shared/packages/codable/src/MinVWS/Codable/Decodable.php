<?php

namespace MinVWS\Codable;

/**
 * Decodable.
 *
 * @package MinVWS\Codable
 */
interface Decodable
{
    /**
     * Decode to this class.
     *
     * @param DecodingContainer  $container Decoding container.
     * @param static|object|null $object    Decode into the given object.
     *
     * @return static
     *
     * @throws CodableException
     */
    public static function decode(DecodingContainer $container, ?object $object = null); // :static only supported since PHP 8
}
