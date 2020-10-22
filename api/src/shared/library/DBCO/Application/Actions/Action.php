<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Actions;

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
    public function __invoke(Request $request, Response $response, array $args): Response
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
     * @param \DBCO\Shared\Application\Responses\Response $response Response.
     *
     * @return Response
     */
    protected function respond(\DBCO\Shared\Application\Responses\Response $response): Response
    {
        return $response->respond($this->response);
    }
}
