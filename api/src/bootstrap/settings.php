<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    // Global Settings Object
    $containerBuilder->addDefinitions([
        'settings' => [
            'displayErrorDetails' => true, // TODO: make env, set to false in production
            'logErrors' => true,
            'logErrorDetails' => true,
            'logger' => [
                'name' => 'api',
                'path' => 'php://stdout',
                'level' => Logger::DEBUG,
            ]
        ]
    ]);
};
