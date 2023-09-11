<?php

namespace MinVWS\Codable;

/**
 * External encodable implementation for class.
 *
 * @package MinVWS\Codable
 */
interface StaticEncodableDecorator
{
    /**
     * Encode to the given class.
     *
     * @param object            $object
     * @param EncodingContainer $container
     *
     * @throws CodableException
     */
    public static function encode(object $object, EncodingContainer $container): void;
}
