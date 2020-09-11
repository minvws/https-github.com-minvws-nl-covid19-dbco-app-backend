<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use function DI\env;

use Monolog\Logger;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        'settings' => [
            'logger' => [
                'name' => 'console',
                'path' => 'php://stdout',
                'level' => Logger::DEBUG,
            ]
        ]
    ]);
};
