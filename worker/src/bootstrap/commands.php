<?php
declare(strict_types=1);

use DBCO\Worker\Application\Commands\RefreshGeneralTasksCommand;
use DBCO\Worker\Application\Commands\RefreshQuestionnairesCommand;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

return function (Application $app, ContainerInterface $container) {
    $commands = [
        RefreshQuestionnairesCommand::class,
        RefreshGeneralTasksCommand::class,
    ];

    $commandMap = [];
    foreach ($commands as $class) {
        $commandMap[$class::getDefaultName()] = $class;
    }

    $app->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
};
