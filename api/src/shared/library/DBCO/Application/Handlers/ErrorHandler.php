<?php
declare(strict_types=1);

namespace DBCO\Application\Handlers;

use DBCO\Application\Actions\ActionException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;

/**
 * Error handler.
 *
 * @package DBCO\Application\Handlers
 */
class ErrorHandler extends SlimErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;

        if (!($exception instanceof ActionException)) {
            $message = 'An error occurred while processing your request. Please try again later.';
            if ($this->displayErrorDetails) {
                $message .= "\n" . $exception->getMessage() . "\n" . $exception->getTraceAsString();
            }
            $exception = new ActionException($this->request, 'internalError', $message, ActionException::INTERNAL_SERVER_ERROR, $exception);
        }

        $response = $this->responseFactory->createResponse($exception->getCode());
        $json = json_encode($exception, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
