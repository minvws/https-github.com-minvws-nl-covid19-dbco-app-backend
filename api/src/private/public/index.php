<?php
declare(strict_types=1);

use DBCO\Shared\Application\Handlers\ShutdownHandler;
use Slim\Factory\ServerRequestCreatorFactory;

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
if ($debug) {
    ini_set('display_errors', '1');
}

define('APP_ROOT', __DIR__ . '/..');

$app = require APP_ROOT . '/bootstrap/application.php';

// Create Request object from globals
$serverRequestCreator = ServerRequestCreatorFactory::create();
$request = $serverRequestCreator->createServerRequestFromGlobals();

ShutdownHandler::register($app, $request);

// Run App & Emit Response
$app->run($request);