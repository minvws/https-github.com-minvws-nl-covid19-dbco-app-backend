<?php
namespace DBCO\Shared\Application\Codable;

/**
 * External decodable implementation for class.
 *
 * @package DBCO\Shared\Application\Codable
 */
interface DecodableDecorator
{
    /**
     * Decode to the given class.
     *
     * @template T
     *
     * @param class-string<T>   $class
     * @param DecodingContainer $container
     *
     * @return T
     *
     * @throws DecodeException
     */
    public static function decode(string $class, DecodingContainer $container): object;
}
