<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Actions;

use Exception;
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
     * Invoke action.
     *
     * @param Request  $request
     * @param Response $response
     * @param array    $args
     *
     * @return Response
     *
     * @throws ActionException
     */
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $this->logger->info('Invoke action ' . get_class($this) . ' for ' . strtoupper($request->getMethod()) . ' ' . $request->getUri());

        $this->request = $request;
        $this->response = $response;
        $this->args = $args;

        try {
            $result = $this->action();
            $this->logger->info('Finished action ' . get_class($this) . ' with status ' . $result->getStatusCode());
            return $result;
        } catch (Exception $e) {
            if (!($e instanceof ActionException) || $e->getCode() === ActionException::INTERNAL_SERVER_ERROR) {
                $this->logger->error('Finished action ' . get_class($this) . ' with error: ' . $e->getMessage());
            } else {
                $this->logger->info('Finished action ' . get_class($this) . ' with status ' . $e->getCode());
            }

            throw $e;
        }
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
