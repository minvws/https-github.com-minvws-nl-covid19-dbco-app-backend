<?php
declare(strict_types=1);

namespace DBCO\Application\Actions;

/**
 * Validation exception.
 *
 * @package DBCO\Application\Actions
 */
class ValidationError
{
    /**
     * @var string
     */
    private string $code;

    /**
     * @var string
     */
    private string $message;

    /**
     * @var string[]
     */
    private array $path;

    /**
     * Body error.
     *
     * @param string   $code
     * @param string   $message
     * @param string[] $path
     *
     * @return ValidationError
     */
    public static function body(string $code, string $message, array $path): self
    {
        return new ValidationError($code, $message, $path);
    }

    /**
     * Header error.
     *
     * @param string $code    Error code.
     * @param string $message Error message.
     * @param string $header  Header name.
     *
     * @return ValidationError
     */
    public static function header($code, $message, $header): self
    {
        return new ValidationError($code, $message, ['$header', $header]);
    }

    /**
     * URL path error.
     *
     * @param string $code    Error code.
     * @param string $message Error message.
     * @param string $arg     URL path argument.
     *
     * @return ValidationError
     */
    public static function url($code, $message, $arg): self
    {
        return new ValidationError($code, $message, ['$url', $arg]);
    }

    /**
     * Query parameter error.
     *
     * @param string $code    Error code.
     * @param string $message Error message.
     * @param string $name    Query parameter name.
     *
     * @return ValidationError
     */
    public static function query($code, $message, $name): self
    {
        return new ValidationError($code, $message, ['$query', $name]);
    }

    /**
     * Constructs a new error.
     *
     * @param string   $code    Error code.
     * @param string   $message Error message.
     * @param string[] $path    Error path.
     */
    protected function __construct(string $code, string $message, array $path)
    {
        $this->code = $code;
        $this->message = $message;
        $this->path = $path;
    }

    /**
     * Prepare for JSON serialization.
     *
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'code' => $this->code,
            'message' => $this->message,
            'path' => $this->path
        ];
    }
}
