<?php
declare(strict_types=1);

namespace DBCO\Application\Actions;

use Exception;
use JsonSerializable;
use Slim\Psr7\Request;
use Throwable;

/**
 * Action exception.
 *
 * @package DBCO\Application\Actions
 */
class ActionException extends Exception implements JsonSerializable
{
    public const BAD_REQUEST           = 400;
    public const INTERNAL_SERVER_ERROR = 501;

    /**
     * @var Request
     */
    private Request $request;

    /**
     * @var string
     */
    private string $type;

    /**
     * Constructs a new error.
     *
     * @param Request        $request  Request.
     * @param string         $type     Error type.
     * @param string         $message  Error message.
     * @param int            $code     Request status code.
     * @param Throwable|null $previous Previous exception.
     */
    public function __construct(Request $request, $type, string $message, int $code = self::INTERNAL_SERVER_ERROR, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->request = $request;
        $this->type = $type;
    }

    /**
     * Returns the request.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Returns the error type.
     *
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return [
            'type' => $this->getType(),
            'message' => $this->getMessage(),
        ];
    }
}
