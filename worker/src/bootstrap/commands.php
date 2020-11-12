<?php
declare(strict_types=1);

use DBCO\Worker\Application\Commands\ProcessPairingQueueCommand;
use DBCO\Worker\Application\Commands\RefreshGeneralTasksCommand;
use DBCO\Worker\Application\Commands\RefreshQuestionnairesCommand;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;

return function (Application $app, ContainerInterface $container) {
    $app->add($container->get(RefreshQuestionnairesCommand::class));
    $app->add($container->get(RefreshGeneralTasksCommand::class));
    $app->add($container->get(ProcessPairingQueueCommand::class));
};
