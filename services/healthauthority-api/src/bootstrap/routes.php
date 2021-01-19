<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Actions\CaseExportAction;
use DBCO\HealthAuthorityAPI\Application\Actions\CaseSubmitAction;
use DBCO\HealthAuthorityAPI\Application\Actions\ClientRegisterAction;
use DBCO\HealthAuthorityAPI\Application\Actions\GeneralTaskListAction;
use DBCO\HealthAuthorityAPI\Application\Actions\QuestionnaireListAction;
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

    $app->get('/v1/questionnaires', QuestionnaireListAction::class);
    $app->get('/v1/tasks', GeneralTaskListAction::class);

    $app->post('/v1/cases/{caseUuid}/exports', CaseExportAction::class);
    $app->post('/v1/cases/{caseUuid}/clients', ClientRegisterAction::class);
    $app->put('/v1/cases/{token}', CaseSubmitAction::class);

    $app->get('/v1/ping', PingAction::class); // available versioned (clients)
    $app->get('/ping', PingAction::class); // and unversioned (kubernetes)
    $app->get('/status', HealthCheckAction::class);
};
