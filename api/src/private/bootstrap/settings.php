<?php
declare(strict_types=1);

use Monolog\Logger;
use Psr\Container\ContainerInterface;

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

return [
    'errorHandler.displayErrorDetails' => $debug,
    'errorHandler.logErrors' => true,
    'errorHandler.logErrorDetails' => $debug,

    'logger.name' => 'api',
    'logger.path' => 'php://stdout',
    'logger.level' => $debug ? Logger::DEBUG : Logger::ERROR,

    'pairingCode.allowedChars' => '1234567890',
    'pairingCode.length' => 12,
    'pairingCode.expiresDelta' => DI\env('PAIRING_CODE_EXPIRES_DELTA', 45 * 60), // 45 minutes
    'pairingCode.expiredWarningDelta' => 24 * 60 * 60, // 1 day
    'pairingCode.blockedDelta' => 30 * 24 * 60 * 60, // 30 days

    'redis.connection' => [
        'host' => DI\env('REDIS_HOST'),
        'port' => DI\env('REDIS_PORT')
    ],
    'redis.parameters' =>
        DI\factory(function (ContainerInterface $c) {
            $service = getenv('REDIS_SENTINEL_SERVICE');
            if (empty($service)) {
                return $c->get('redis.connection');
            } else {
                return [$c->get('redis.connection')];
            }
        }),
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

    'jwt' => [
        'enabled'   => filter_var(getenv('JWT_ENABLED'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'attribute' => 'jwtClaims',
        'path'      => ['/v1'],
        'secure'    => filter_var(getenv('JWT_SECURE'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true,
        'secret'    => getenv('JWT_SECRET')
    ]
];
