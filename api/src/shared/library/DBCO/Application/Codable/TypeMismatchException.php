<?php
namespace DBCO\Shared\Application\Codable;

/**
 * Unexpected type error.
 *
 * @package DBCO\Shared\Application\Codable
 */
class TypeMismatchException extends DecodePathException
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
        parent::__construct($path, "Unexpected type '{$type}' at path '" . static::convertPathToString($path) . "', expected type: '$expectedType'");
    }
}