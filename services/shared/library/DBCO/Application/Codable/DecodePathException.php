<?php
namespace DBCO\Shared\Application\Codable;

use Exception;

/**
 * Base exception class for decoding errors at a certain path.
 *
 * @package DBCO\Shared\Application\Codable
 */
class DecodePathException extends DecodeException
{
    /**
     * @var string[]
     */
    private array $path;

    /**
     * Constructor.
     *
     * @param string[] $path
     * @param string   $message
     */
    public function __construct(array $path, string $message)
    {
        parent::__construct($message);
        $this->path = $path;
    }

    /**
     * Convert path to string.
     *
     * @param string[] $path
     *
     * @return string
     */
    public static function convertPathToString(array $path): string
    {
        $result = '';

        foreach ($path as $key) {
            if (is_int($key)) {
                $result .= "[{$key}]";
            } else {
                $result .= (strlen($result) > 0 ? '.' : '') . $key;
            }
        }

        return $result;
    }

    /**
     * Returns the path.
     *
     * @return string[]
     */
    function getPath(): array
    {
        return $this->path;
    }

    /**
     * Returns the path as string.
     *
     * @return string
     */
    function getPathAsString(): string
    {
        return static::convertPathToString($this->path);
    }
}