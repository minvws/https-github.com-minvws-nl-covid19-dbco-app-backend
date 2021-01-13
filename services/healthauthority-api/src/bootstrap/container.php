<?php
declare(strict_types=1);

use DI\ContainerBuilder;

require __DIR__ . '/../vendor/autoload.php';

// Instantiate PHP-DI ContainerBuilder
$containerBuilder = new ContainerBuilder();

if (getenv('APP_ENV') === 'production') {
	$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');
}

// Set up settings
$settings = require __DIR__ . '/settings.php';
$containerBuilder->addDefinitions($settings);

// Set up dependencies
$dependencies = require __DIR__ . '/dependencies.php';
$dependencies($containerBuilder);

// Set up repositories
$repositories = require __DIR__ . '/repositories.php';
$repositories($containerBuilder);

// Set up services
$services = require __DIR__ . '/services.php';
$services($containerBuilder);

// Build and return PHP-DI Container instance
return $containerBuilder->build();