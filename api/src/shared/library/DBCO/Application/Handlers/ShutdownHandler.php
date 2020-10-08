<?php
declare(strict_types=1);

namespace DBCO\Application\Handlers;

use DBCO\Application\Actions\ActionException;
use DBCO\Application\ResponseEmitter\ResponseEmitter;
use Psr\Http\Message\ServerRequestInterface as Request;

/**
 * Handle PHP errors on shutdown.
 *
 * @package DBCO\Application\Handlers
 */
class ShutdownHandler
{
    /**
     * @var Request
     */
    private $request;

    /**
     * @var ErrorHandler
     */
    private $errorHandler;

    /**
     * @var bool
     */
    private $displayErrorDetails;

    /**
     * ShutdownHandler constructor.
     *
     * @param Request       $request
     * @param $errorHandler $errorHandler
     * @param bool          $displayErrorDetails
     */
    public function __construct(
        Request $request,
        ErrorHandler $errorHandler,
        bool $displayErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
    }

    /**
     * Invoke.
     */
    public function __invoke()
    {
        $error = error_get_last();
        if (!$error) {
            return;
        }

        $errorFile = $error['file'];
        $errorLine = $error['line'];
        $errorMessage = $error['message'];
        $errorType = $error['type'];
        $message = 'An error occurred while processing your request. Please try again later.';

        if ($this->displayErrorDetails) {
            switch ($errorType) {
                case E_USER_ERROR:
                    $message = "FATAL ERROR: {$errorMessage}. ";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;

                case E_USER_WARNING:
                    $message = "WARNING: {$errorMessage}";
                    break;

                case E_USER_NOTICE:
                    $message = "NOTICE: {$errorMessage}";
                    break;

                default:
                    $message = "ERROR: {$errorMessage}";
                    $message .= " on line {$errorLine} in file {$errorFile}.";
                    break;
            }
        }

        $exception = new ActionException($this->request, 'internalError', $message);
        $response = $this->errorHandler->__invoke($this->request, $exception, $this->displayErrorDetails, false, false);

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }
}
