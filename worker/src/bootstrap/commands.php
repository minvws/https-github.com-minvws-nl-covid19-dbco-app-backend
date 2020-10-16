<?php
declare(strict_types=1);

use App\Application\Commands\RefreshGeneralTasksCommand;
use App\Application\Commands\RefreshQuestionnairesCommand;
use Symfony\Component\Console\Application;
use Psr\Container\ContainerInterface;

return function (Application $app, ContainerInterface $container) {
    $app->add($container->get(RefreshQuestionnairesCommand::class));
    $app->add($container->get(RefreshGeneralTasksCommand::class));
};
