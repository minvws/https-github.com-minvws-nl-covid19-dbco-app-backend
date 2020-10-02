<?php
declare(strict_types=1);

use App\Application\Handlers\ErrorHandler;
use Slim\App;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $displayErrorDetails = $app->getContainer()->get('displayErrorDetails');
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);

    $callableResolver = $app->getCallableResolver();
    $responseFactory = $app->getResponseFactory();
    $errorHandler = new ErrorHandler($callableResolver, $responseFactory);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
};
