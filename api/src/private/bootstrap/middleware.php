<?php
declare(strict_types=1);

use DBCO\PrivateAPI\Application\Helpers\JWTConfigHelper;
use DBCO\Shared\Application\Handlers\ErrorHandler;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $jwtConfigHelper = $app->getContainer()->get(JWTConfigHelper::class);
    if ($jwtConfigHelper->isEnabled()) {
        $app->add(new JwtAuthentication($jwtConfigHelper->getConfig()));
    }

    $displayErrorDetails = $app->getContainer()->get('errorHandler.displayErrorDetails');
    $logErrors = $app->getContainer()->get('errorHandler.logErrors');
    $logErrorDetails = $app->getContainer()->get('errorHandler.logErrorDetails');
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, $logErrors, $logErrorDetails);

    $callableResolver = $app->getCallableResolver();
    $responseFactory = $app->getResponseFactory();
    $errorHandler = new ErrorHandler($callableResolver, $responseFactory);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
};
