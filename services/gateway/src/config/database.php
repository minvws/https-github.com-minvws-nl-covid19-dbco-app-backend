<?php

declare(strict_types=1);

use Illuminate\Support\Str;

$createSingleRedisConfig = function (string $prefix = 'REDIS_') {
    return [
        'host' => env($prefix . 'HOST', '127.0.0.1'),
        'username' => env($prefix . 'USERNAME', null),
        'password' => env($prefix . 'PASSWORD', null),
        'port' => env($prefix . 'PORT', '6379')
    ];
};

$createSentinelRedisConfig = function (string $prefix = 'REDIS_') {
    $hostname = env($prefix . 'HOST');
    $ipAddresses = gethostbynamel($hostname);

    if ($ipAddresses === false) {
        $ipAddresses = [];
    }

    $sentinels = [];
    foreach ($ipAddresses as $ipAddress) {
        $sentinels[] = "tcp://{$ipAddress}:" . env($prefix . 'PORT', '26379');
    }

    return [
        ...$sentinels,
        'options' => [
            'replication' => 'sentinel',
            'service' => env($prefix . 'SENTINEL_SERVICE'),
            'parameters' => [
                'username' => env($prefix . 'USERNAME', null),
                'password' => env($prefix . 'PASSWORD', null),
            ]
        ]
    ];
};

$createRedisConfig = function (string $prefix = 'REDIS_') use ($createSingleRedisConfig, $createSentinelRedisConfig) {
    $redisSentinelService = env($prefix . 'SENTINEL_SERVICE');
    return empty($redisSentinelService) ? $createSingleRedisConfig($prefix) : $createSentinelRedisConfig($prefix);
};

$prometheus = empty(env('REDIS_PROMETHEUS_HOST')) ?
    $createRedisConfig() :
    $createRedisConfig('REDIS_PROMETHEUS_');

return [
    /*
    |--------------------------------------------------------------------------
    | Redis Databases
    |--------------------------------------------------------------------------
    |
    | Redis is an open source, fast, and advanced key-value store that also
    | provides a richer body of commands than a typical key-value system
    | such as APC or Memcached. Laravel makes it easy to dig right in.
    |
    */
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),
        'options' => [
            'cluster' => env('REDIS_CLUSTER', 'redis'),
            'prefix' => env('REDIS_PREFIX', Str::slug(env('APP_NAME', 'laravel'), '_') . '_database_'),
        ],
        'prometheus' => $prometheus
    ],
];
