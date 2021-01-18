<?php
declare(strict_types=1);

use DBCO\Shared\Application\Actions\HealthCheckAction;
use DBCO\Shared\Application\Actions\PingAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

use DBCO\PrivateAPI\Application\Actions\CaseRegisterAction;
use DBCO\PrivateAPI\Application\Actions\CaseUpdateAction;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->post('/v1/cases', CaseRegisterAction::class);
    $app->put('/v1/cases/{token}', CaseUpdateAction::class);

    $app->get('/v1/ping', PingAction::class); // available versioned (clients)
    $app->get('/ping', PingAction::class); // and unversioned (kubernetes)
    $app->get('/status', HealthCheckAction::class);
};
