<?php
declare(strict_types=1);

use DBCO\Bridge\Application\Commands\ProcessQueueCommand;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

return function (Application $app, ContainerInterface $container) {
    $lanes = require(__DIR__ . '/lanes.php');

    $commandMap = [];
    foreach ($lanes as $lane) {
        $commandMap['process:' . $lane['name']] = 'lane.' . $lane['name'] . '.command';
    }

    $app->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
};
