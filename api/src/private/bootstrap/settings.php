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

    'pairingCode.allowedChars' => '0123456789',
    'pairingCode.length' => 9,
    'pairingCode.timeToLive' => 900, // 15 minutes

    'redis' => [
        'host' => DI\env('REDIS_HOST'),
        'port' => DI\env('REDIS_PORT')
    ],

    'jwt' => [
        'enabled'   => filter_var(getenv('JWT_ENABLED'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'attribute' => 'jwtClaims',
        'path'      => ['/v1'],
        'secure'    => filter_var(getenv('JWT_SECURE'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'secret'    => getenv('JWT_SECRET')
    ]
];
