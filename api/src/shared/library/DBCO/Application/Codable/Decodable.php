<?php
namespace DBCO\Shared\Application\Codable;

/**
 * Decodable.
 *
 * @package DBCO\Shared\Application\Codable
 */
interface Decodable
{
    /**
     * Decode to this class.
     *
     * @param DecodingContainer $container
     *
     * @return self
     *
     * @throws DecodeException
     */
    public static function decode(DecodingContainer $container): self;
}