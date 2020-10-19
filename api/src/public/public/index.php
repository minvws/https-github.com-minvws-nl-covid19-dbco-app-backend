<?php

declare(strict_types=1);

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
if ($debug) {
    ini_set('display_errors', '1');
}

define('APP_ROOT', __DIR__ . '/..');

use DBCO\Application\Handlers\ErrorHandler;
use DBCO\Application\Handlers\ShutdownHandler;
use DBCO\Application\ResponseEmitter\ResponseEmitter;
use Slim\Factory\ServerRequestCreatorFactory;

$app = require APP_ROOT . '/bootstrap/application.php';

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

// Create Shutdown Handler
$errorHandler = new ErrorHandler($app->getCallableResolver(), $app->getResponseFactory());
$displayErrorDetails = $app->getContainer()->get('displayErrorDetails');
$shutdownHandler = new ShutdownHandler($request, $errorHandler, $displayErrorDetails);
register_shutdown_function($shutdownHandler);

// Run App & Emit Response
$response = $app->handle($request);
$responseEmitter = new ResponseEmitter();
$responseEmitter->emit($response);
