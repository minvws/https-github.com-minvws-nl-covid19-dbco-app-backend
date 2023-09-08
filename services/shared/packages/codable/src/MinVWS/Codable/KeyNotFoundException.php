<?php

namespace MinVWS\Codable;

/**
 * Unexpected null key error.
 *
 * @package MinVWS\Codable
 */
class KeyNotFoundException extends CodablePathException
{
    /**
     * Constructor.
     *
     * @param array $path
     */
    public function __construct(array $path)
    {
        parent::__construct($path, "Key not found at path '" . static::convertPathToString($path) . "'");
    }
}
