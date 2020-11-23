<?php
namespace DBCO\Shared\Application\Codable;

/**
 * Unexpected null value error.
 *
 * @package DBCO\Shared\Application\Codable
 */
class ValueNotFoundException extends DecodePathException
{
    /**
     * Constructor.
     *
     * @param array $path
     */
    public function __construct(array $path)
    {
        parent::__construct($path,"Value not found at path '" . static::convertPathToString($path) . "'");
    }
}