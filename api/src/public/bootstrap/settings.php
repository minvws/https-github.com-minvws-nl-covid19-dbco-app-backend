<?php
declare(strict_types=1);

use Monolog\Logger;

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

return [
    'errorHandler.displayErrorDetails' => $debug,
    'errorHandler.logErrors' => true,
    'errorHandler.logErrorDetails' => $debug,

    'logger.name' => 'api',
    'logger.path' => 'php://stdout',
    'logger.level' => $debug ? Logger::DEBUG : Logger::ERROR,

    'redis.parameters' => [
        [
            'host' => DI\env('REDIS_HOST'),
            'port' => DI\env('REDIS_PORT')
        ]
    ],
    'redis.options' =>
        DI\factory(function () {
            $service = getenv('REDIS_SENTINEL_SERVICE');

            $options = [];
            if (!empty($service)) {
                $options['replication'] = 'sentinel';
                $options['service'] = $service;
            }

            return $options;
        }),

    'signingKey.length' => 32
];
