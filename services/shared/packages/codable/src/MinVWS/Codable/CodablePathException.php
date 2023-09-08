<?php

namespace MinVWS\Codable;

/**
 * Base exception class for encoding/decoding errors at a certain path.
 *
 * @package MinVWS\Codable
 */
class CodablePathException extends CodableException
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
    public function getPath(): array
    {
        return $this->path;
    }

    /**
     * Returns the path as string.
     *
     * @return string
     */
    public function getPathAsString(): string
    {
        return static::convertPathToString($this->path);
    }
}
