<?php
declare(strict_types=1);

namespace App\Application\Handlers;

use App\Application\Actions\ActionException;
use Exception;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

/**
 * Error handler.
 *
 * @package App\Application\Handlers
 */
class ErrorHandler extends SlimErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;

        if (
            !($exception instanceof ActionException)
            && ($exception instanceof Exception || $exception instanceof Throwable)
            && $this->displayErrorDetails
        ) {
            $message = 'An error occurred while processing your request. Please try again later.';
            $exception = new ActionException($this->request, 'internalError', $message, ActionException::INTERNAL_SERVER_ERROR, $exception);
        }

        $response = $this->responseFactory->createResponse($exception->getCode());
        $json = json_encode($exception, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
