<?php
declare(strict_types=1);

use Slim\Factory\AppFactory;

$container = require __DIR__ . '/container.php';

// Instantiate the app
AppFactory::setContainer($container);
$app = AppFactory::create();

// Register middleware
$middleware = require __DIR__ . '/middleware.php';
$middleware($app);

// Register routes
$routes = require __DIR__ . '/routes.php';
$routes($app);

return $app;
