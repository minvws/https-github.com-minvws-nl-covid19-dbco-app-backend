<?php
declare(strict_types=1);

use DBCO\PublicAPI\Application\Actions\CaseAction;
use DBCO\PublicAPI\Application\Actions\CaseSubmitAction;
use DBCO\PublicAPI\Application\Actions\ConfigAction;
use DBCO\PublicAPI\Application\Actions\GeneralTaskListAction;
use DBCO\PublicAPI\Application\Actions\PairingAction;
use DBCO\PublicAPI\Application\Actions\QuestionnaireListAction;
use DBCO\Shared\Application\Actions\HealthCheckAction;
use DBCO\Shared\Application\Actions\PingAction;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\App;

return function (App $app) {
    $app->options('/{routes:.*}', function (Request $request, Response $response) {
        // CORS Pre-Flight OPTIONS Request Handler
        return $response;
    });

    $app->get('/v1/config', ConfigAction::class);

    $app->post('/v1/pairings', PairingAction::class);

    $app->get('/v1/questionnaires', QuestionnaireListAction::class);
    $app->get('/v1/tasks', GeneralTaskListAction::class);

    $app->get('/v1/cases/{token}', CaseAction::class);
    $app->put('/v1/cases/{token}', CaseSubmitAction::class);

    $app->get('/v1/ping', PingAction::class);
    $app->get('/status', HealthCheckAction::class);
};
