<?php
declare(strict_types=1);

use DBCO\Shared\Application\Handlers\ErrorHandler;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    // TODO: disabled for now, not sure yet if we need this
    //$app->add(new JwtAuthentication($app->getContainer()->get('jwt')));

    $displayErrorDetails = $app->getContainer()->get('displayErrorDetails');
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);

    $callableResolver = $app->getCallableResolver();
    $responseFactory = $app->getResponseFactory();
    $errorHandler = new ErrorHandler($callableResolver, $responseFactory);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
};
