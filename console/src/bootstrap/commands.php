<?php
declare(strict_types=1);

use Symfony\Component\Console\Application;
use App\Application\Commands\ExampleCommand;
use Psr\Container\ContainerInterface;

return function (Application $app, ContainerInterface $container) {
    $app->add($container->get(ExampleCommand::class));
};
