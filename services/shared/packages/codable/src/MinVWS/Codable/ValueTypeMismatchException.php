<?php

namespace MinVWS\Codable;

/**
 * Unexpected value type error.
 *
 * @package MinVWS\Codable
 */
class ValueTypeMismatchException extends CodablePathException
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
        parent::__construct($path, "Unexpected value type '{$type}' at path '" . static::convertPathToString($path) . "', expected type: '$expectedType'");
    }
}
