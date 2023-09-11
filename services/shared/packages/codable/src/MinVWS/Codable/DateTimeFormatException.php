<?php

namespace MinVWS\Codable;

/**
 * Unexpected date/time format.
 *
 * @package MinVWS\Codable
 */
class DateTimeFormatException extends CodablePathException
{
    /**
     * Constructor.
     *
     * @param array  $path
     * @param string $format
     */
    public function __construct(array $path, string $format)
    {
        parent::__construct($path, "Invalid date/time format at path '" . static::convertPathToString($path) . "', format '{$format}'");
    }
}
