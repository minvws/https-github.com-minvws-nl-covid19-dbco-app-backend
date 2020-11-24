<?php
declare(strict_types=1);

use DBCO\Shared\Application\Handlers\ErrorHandler;
use DBCO\Shared\Application\Handlers\ShutdownHandler;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    // TODO: disabled for now, not sure yet if we need this
    //$app->add(new JwtAuthentication($app->getContainer()->get('jwt')));

    $displayErrorDetails = $app->getContainer()->get('errorHandler.displayErrorDetails');
    $logErrors = $app->getContainer()->get('errorHandler.logErrors');
    $logErrorDetails = $app->getContainer()->get('errorHandler.logErrorDetails');
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);
    $logger = $app->getContainer()->get(LoggerInterface::class);

    $callableResolver = $app->getCallableResolver();
    $responseFactory = $app->getResponseFactory();
    $errorHandler = new ErrorHandler($callableResolver, $responseFactory, $logger);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
};
