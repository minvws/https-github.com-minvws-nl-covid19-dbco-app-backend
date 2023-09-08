<?php

namespace MinVWS\Codable;

/**
 * Encodable.
 *
 * @package MinVWS\Codable
 */
interface Encodable
{
    /**
     * Encode.
     *
     * @param EncodingContainer $container
     *
     * @throws CodableException
     */
    public function encode(EncodingContainer $container): void;
}
