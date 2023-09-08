<?php

namespace MinVWS\Codable;

/**
 * Unexpected null value error.
 *
 * @package MinVWS\Codable
 */
class ValueNotFoundException extends CodablePathException
{
    /**
     * Constructor.
     *
     * @param array $path
     */
    public function __construct(array $path)
    {
        parent::__construct($path, "Value not found at path '" . static::convertPathToString($path) . "'");
    }
}
