<?php
declare(strict_types=1);

use App\Application\Helpers\JWTConfigHelper;
use DBCO\Application\Handlers\ErrorHandler;
use Slim\App;
use Tuupola\Middleware\JwtAuthentication;

return function (App $app) {
    $app->addBodyParsingMiddleware();
    $app->addRoutingMiddleware();

    $jwtConfigHelper = $app->getContainer()->get(JWTConfigHelper::class);
    if ($jwtConfigHelper->isEnabled()) {
        $app->add(new JwtAuthentication($jwtConfigHelper->getConfig()));
    }

    $displayErrorDetails = $app->getContainer()->get('displayErrorDetails');
    $errorMiddleware = $app->addErrorMiddleware($displayErrorDetails, false, false);

    $callableResolver = $app->getCallableResolver();
    $responseFactory = $app->getResponseFactory();
    $errorHandler = new ErrorHandler($callableResolver, $responseFactory);
    $errorMiddleware->setDefaultErrorHandler($errorHandler);
};
