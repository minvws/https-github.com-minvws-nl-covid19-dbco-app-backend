<?php

namespace MinVWS\Codable;

/**
 * Unexpected key type error.
 *
 * @package MinVWS\Codable
 */
class KeyTypeMismatchException extends CodablePathException
{
    /**
     * Constructor.
     *
     * @param array  $path
     * @param string $type
     * @param string $expectedType
     */
    public function __construct(array $path, string $type, string $expectedType)
    {
        parent::__construct($path, "Unexpected key type '{$type}' at path '" . static::convertPathToString($path) . "', expected type: '$expectedType'");
    }
}
