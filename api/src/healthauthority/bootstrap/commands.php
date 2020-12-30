<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Commands\CreateKeyExchangeSecretKeyCommand;
use DBCO\HealthAuthorityAPI\Application\Commands\CreateStoreSecretKeysCommand;
use DBCO\HealthAuthorityAPI\Application\Commands\GetKeyExchangePublicKeyCommand;
use DBCO\HealthAuthorityAPI\Application\Commands\ManageKeysCommand;
use DBCO\Shared\Application\ConsoleApplication;
use DBCO\HealthAuthorityAPI\Application\Commands\CacheKeysCommand;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\CommandLoader\ContainerCommandLoader;

return function (ConsoleApplication $app, ContainerInterface $container) {
    $commands = [
        CreateStoreSecretKeysCommand::class,
        CreateKeyExchangeSecretKeyCommand::class,
        GetKeyExchangePublicKeyCommand::class,
        CacheKeysCommand::class,
        ManageKeysCommand::class
    ];

    $commandMap = [];
    foreach ($commands as $class) {
        $commandMap[$class::getDefaultName()] = $class;
    }

    $app->setCommandLoader(new ContainerCommandLoader($container, $commandMap));
};
