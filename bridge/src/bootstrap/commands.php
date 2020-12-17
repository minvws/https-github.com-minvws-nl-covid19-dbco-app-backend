<?php
declare(strict_types=1);

use DBCO\Bridge\Application\Commands\ProcessQueueCommand;
use DBCO\Bridge\Application\Commands\StatusCommand;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

return function (Application $app, ContainerInterface $container) {
    $commandMap = [];

    $commandMap[StatusCommand::getDefaultName()] = StatusCommand::class;

    $lanes = require(__DIR__ . '/lanes.php');
    foreach ($lanes as $lane) {
        $commandMap['process:' . $lane['name']] = 'lane.' . $lane['name'] . '.command';
    }

    $app->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
};
