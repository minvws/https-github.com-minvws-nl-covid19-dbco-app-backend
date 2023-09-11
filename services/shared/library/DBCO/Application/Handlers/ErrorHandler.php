<?php

declare(strict_types=1);

namespace DBCO\Shared\Application\Handlers;

use DBCO\Shared\Application\Actions\ActionException;
use Error;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Handlers\ErrorHandler as SlimErrorHandler;
use Throwable;

/**
 * Error handler.
 *
 * @package DBCO\Shared\Application\Handlers
 */
class ErrorHandler extends SlimErrorHandler
{
    /**
     * @inheritdoc
     */
    protected function writeToErrorLog(): void
    {
        if ($this->displayErrorDetails) {
            parent::writeToErrorLog(); // log all errors if display error details is on
        } elseif (!($this->exception instanceof ActionException)) {
            parent::writeToErrorLog(); // unexpected error
        } elseif ($this->exception->getCode() === ActionException::INTERNAL_SERVER_ERROR) {
            parent::writeToErrorLog(); // explicit internal error
        }
    }

    /**
     * @inheritdoc
     */
    protected function respond(): Response
    {
        $exception = $this->exception;

        if (!($exception instanceof ActionException)) {
            $message = 'An error occurred while processing your request. Please try again later.';
            if ($this->displayErrorDetails) {
                $message .= "\n\n" . $this->getErrorDetails($exception);
            }
            $exception = new ActionException($this->request, 'internalError', $message, ActionException::INTERNAL_SERVER_ERROR, $exception);
        }

        $response = $this->responseFactory->createResponse($exception->getCode());
        $json = json_encode($exception, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response->withHeader('Content-Type', 'application/json');
    }

    /**
     * Get error details.
     *
     * @param Throwable $exception
     *
     * @return string
     */
    private function getErrorDetails(Throwable $exception): string
    {
        if ($exception instanceof Error || $exception instanceof PHPError) {
            $message = "PHP error: " . $exception->getMessage() . "\n" .
                $exception->getFile() . ' at line ' . $exception->getLine() . "\n" .
                $exception->getTraceAsString();
        } else {
            $message = "Exception: " . $exception->getMessage() . "\n" .
                $exception->getTraceAsString();
        }

        $previous = $exception->getPrevious();
        if ($previous !== null) {
            $message .= "\n\nNested " . $this->getErrorDetails($previous);
        }

        return $message;
    }
}
