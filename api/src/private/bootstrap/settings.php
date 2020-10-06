<?php
declare(strict_types=1);

use Monolog\Logger;

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

return [
    'pairingCode.allowedChars' => '0123456789',
    'pairingCode.length' => 9,
    'pairingCode.timeToLive' => 900, // 15 minutes
    'displayErrorDetails' => $debug,
    'logErrors' => true,
    'logErrorDetails' => true,
    'logger.name' => 'api',
    'logger.path' => 'php://stdout',
    'logger.level' => $debug ? Logger::DEBUG : Logger::ERROR,
    'db' => [
        'driver' => 'pgsql',
        'host' => DI\env('DB_HOST'),
        'username' => DI\env('DB_USERNAME'),
        'database' => DI\env('DB_DATABASE'),
        'password' => DI\env('DB_PASSWORD'),
    ],
    'jwt' => [
        'secure' => filter_var(getenv('JWT_SECURE'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'secret' => getenv('JWT_SECRET')
    ]
];
