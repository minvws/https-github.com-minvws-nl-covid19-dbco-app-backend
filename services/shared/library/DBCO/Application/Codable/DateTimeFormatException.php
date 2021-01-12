<?php
namespace DBCO\Shared\Application\Codable;

/**
 * Unexpected date/time format.
 *
 * @package DBCO\Shared\Application\Codable
 */
class DateTimeFormatException extends DecodePathException
{
    /**
     * Constructor.
     *
     * @param array  $path
     * @param string $format
     */
    public function __construct(array $path, string $format)
    {
        parent::__construct($path, "Invalid date/time format at path '" . static::convertPathToString($path) . "', expected format '{$format}'");
    }
}