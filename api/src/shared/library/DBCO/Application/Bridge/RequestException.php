<?php
namespace DBCO\Shared\Application\Bridge;

/**
 * Error response.
 *
 * @package DBCO\Shared\Application\Bridge
 */
class RequestException extends BridgeException
{
    /**
     * @var Response
     */
    private Response $response;

    /**
     * Constructor.
     *
     * @param string   $message
     * @param Response $response
     */
    public function __construct(string $message, Response $response)
    {
        parent::__construct($message);
        $this->response = $response;
    }

    /**
     * Returns the response.
     *
     * @return Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}