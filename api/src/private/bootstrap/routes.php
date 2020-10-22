<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;
use DBCO\PrivateAPI\Application\Actions\CaseRegisterAction;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->post('/v1/cases', CaseRegisterAction::class);

    $app->get('/status', function (Request $request, Response $response) {
        return $response->withStatus(200);
    });
};
