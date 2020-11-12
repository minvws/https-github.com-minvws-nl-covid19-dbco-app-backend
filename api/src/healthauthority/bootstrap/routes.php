<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Actions\CaseAction;
use DBCO\HealthAuthorityAPI\Application\Actions\CaseSubmitAction;
use DBCO\HealthAuthorityAPI\Application\Actions\ClientRegisterAction;
use DBCO\HealthAuthorityAPI\Application\Actions\GeneralTaskListAction;
use DBCO\HealthAuthorityAPI\Application\Actions\QuestionnaireListAction;
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

    $app->post('/v1/cases/{caseUuid}/clients', ClientRegisterAction::class);
    $app->put('/v1/cases/{token}', CaseSubmitAction::class);

    $app->get('/status', function (Request $request, Response $response) {
        return $response->withStatus(200);
    });
};
