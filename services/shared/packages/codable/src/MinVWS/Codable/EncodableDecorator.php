<?php

namespace MinVWS\Codable;

/**
 * External encodable implementation for class.
 *
 * @package MinVWS\Codable
 */
interface EncodableDecorator
{
    /**
     * Encode the given value.
     *
     * @throws CodableException
     */
    public function encode(object $value, EncodingContainer $container): void;
}
