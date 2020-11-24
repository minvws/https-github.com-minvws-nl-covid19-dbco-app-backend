<?php
declare(strict_types=1);

namespace DBCO\Shared\Application\Handlers;

use DBCO\Shared\Application\Actions\ActionException;
use DBCO\Shared\Application\ResponseEmitter\ResponseEmitter;
use Psr\Http\Message\ServerRequestInterface;
use Slim\App;

/**
 * Handle PHP errors on shutdown.
 *
 * @package DBCO\Shared\Application\Handlers
 */
class ShutdownHandler
{
    /**
     * @var ServerRequestInterface
     */
    private ServerRequestInterface $request;

    /**
     * @var ErrorHandler
     */
    private ErrorHandler $errorHandler;

    /**
     * @var bool
     */
    private bool $displayErrorDetails;

    /**
     * Constructor.
     *
     * @param ServerRequestInterface $request
     * @param ErrorHandler           $errorHandler
     * @param bool                   $displayErrorDetails
     * @param bool                   $logErrors
     * @param bool                   $logErrorDetails
     */
    protected function __construct(
        ServerRequestInterface $request,
        ErrorHandler $errorHandler,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) {
        $this->request = $request;
        $this->errorHandler = $errorHandler;
        $this->displayErrorDetails = $displayErrorDetails;
        $this->logErrors = $logErrors;
        $this->logErrorDetails = $logErrorDetails;
    }

    /**
     * Register shutdown function.
     *
     * @param App                    $app
     * @param ServerRequestInterface $request
     */
    public static function register(App $app, ServerRequestInterface $request)
    {
        $errorHandler = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
        $displayErrorDetails = $app->getContainer()->get('errorHandler.displayErrorDetails');
        $logErrors = $app->getContainer()->get('errorHandler.logErrors');
        $logErrorDetails = $app->getContainer()->get('errorHandler.logErrorDetails');
        $shutdownHandler = new self($request, $errorHandler, $displayErrorDetails, $logErrors, $logErrorDetails);
        register_shutdown_function($shutdownHandler);
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

        $message = 'An error occurred while processing your request. Please try again later.';
        $exception = new ActionException($this->request, 'internalError', $message, ActionException::INTERNAL_SERVER_ERROR, new PHPError($error));

        $response = $this->errorHandler->__invoke(
            $this->request,
            $exception,
            $this->displayErrorDetails,
            $this->logErrors,
            $this->logErrorDetails
        );

        $responseEmitter = new ResponseEmitter();
        $responseEmitter->emit($response);
    }
}
