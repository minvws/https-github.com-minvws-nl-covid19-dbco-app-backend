<?php
declare(strict_types=1);

use DBCO\Shared\Application\ConsoleApplication;
use DBCO\HealthAuthorityAPI\Application\Commands\LoadKeysCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

return function (ConsoleApplication $app, ContainerInterface $container) {
    $commands = [
        LoadKeysCommand::class
    ];

    $commandMap = [];
    foreach ($commands as $class) {
        $commandMap[$class::getDefaultName()] = $class;
    }

    $app->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
};
