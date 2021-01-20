<?php
declare(strict_types=1);

use DBCO\Shared\Application\ConsoleApplication;

$container = require __DIR__ . '/container.php';

// Instantiate the console application
$app = new ConsoleApplication();
$app->setContainer($container);

// Register commands
$commands = require __DIR__ . '/commands.php';
$commands($app, $container);

return $app;
