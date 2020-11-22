<?php
declare(strict_types=1);

use Monolog\Logger;

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

return [
    'logger.name' => 'console',
    'logger.path' => 'php://stdout',
    'logger.level' => $debug ? Logger::DEBUG : Logger::ERROR,
    'redis' => [
        'host' => DI\env('REDIS_HOST'),
        'port' => DI\env('REDIS_PORT')
    ],
    'healthAuthorityAPI' => [
        'base_uri' => DI\env('HEALTHAUTHORITY_API_BASE_URI')
    ]
];
