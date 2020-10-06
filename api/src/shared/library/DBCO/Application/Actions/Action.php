<?php
declare(strict_types=1);

namespace DBCO\Application\Actions;

use JsonSerializable;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Log\LoggerInterface;

abstract class Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var array
     */
    protected $args;

    /**
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     * @return Response
     *
     * @throws ActionException
     */
    public function __invoke(Request $request, Response $response, $args): Response
    {
        $this->request = $request;
        $this->response = $response;
        $this->args = $args;
        return $this->action();
    }

    /**
     * Action.
     *
     * @return Response
     *
     * @throws ActionException
     */
    abstract protected function action(): Response;

    /**
     * Respond with response object.
     *
     * @param \App\Application\Responses\Response $response Response.
     *
     * @return Response
     */
    protected function respond(\DBCO\Application\Responses\Response $response): Response
    {
        return $this->respondWithJson($response, $response->getStatusCode());
    }

    /**
     * Respond with JSON.
     *
     * @param mixed|JsonSerializable $data       Data to serialize.
     * @param int                    $statusCode HTTP status code.
     *
     * @return Response
     */
    protected function respondWithJson($data, int $statusCode = 200): Response
    {
        $json = json_encode($data, JSON_PRETTY_PRINT);
        $this->response->getBody()->write($json);
        return $this->response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($statusCode);
    }

    /**
     * Respond with error.
     *
     * @param ActionException $e Error.
     *
     * @return Response
     */
    protected function respondWithError(ActionException $e): Response
    {
        return $this->respondWithJson($e, $e->getCode());
    }
}
