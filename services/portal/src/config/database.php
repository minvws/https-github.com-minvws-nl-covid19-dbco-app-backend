<?php

declare(strict_types=1);

$createSingleRedisConfig = static function (string $prefix = 'REDIS_') {
    return [
        'host' => env($prefix . 'HOST', '127.0.0.1'),
        'username' => env($prefix . 'USERNAME', null),
        'password' => env($prefix . 'PASSWORD', null),
        'port' => env($prefix . 'PORT', '6379'),
    ];
};

$createSentinelRedisConfig = static function (string $prefix = 'REDIS_') {
    $prefixedHost = env($prefix . 'HOST');
    if (!is_string($prefixedHost)) {
        $prefixedHost = '';
    }

    $ipAddresses = gethostbynamel($prefixedHost);

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
            ],
        ],
    ];
};

$createRedisConfig = static function (string $prefix = 'REDIS_') use ($createSingleRedisConfig, $createSentinelRedisConfig) {
    $redisSentinelService = env($prefix . 'SENTINEL_SERVICE');
    if (empty($redisSentinelService)) {
        return $createSingleRedisConfig($prefix);
    }

    return $createSentinelRedisConfig($prefix);
};

$redis = $createRedisConfig();
$redisHSM = $createSingleRedisConfig('REDIS_HSM_');
$redisPrometheus = empty(env('REDIS_PROMETHEUS_HOST')) ? $redis : $createRedisConfig('REDIS_PROMETHEUS_');

return [

    /*
    |--------------------------------------------------------------------------
    | Default Database Connection Name
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the database connections below you wish
    | to use as your default connection for all database work. Of course
    | you may use many connections at once using the Database library.
    |
    */

    'default' => env('DB_CONNECTION', 'mysql'),

    /*
    |--------------------------------------------------------------------------
    | Database Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the database connections setup for your application.
    | Of course, examples of configuring each database platform that is
    | supported by Laravel is shown below to make development simple.
    |
    |
    | All database work in Laravel is done through the PHP PDO facilities
    | so make sure you have the driver for your particular database of
    | choice installed on your machine before you begin development.
    |
    */

    'connections' => [
        'mysql' => [
            'driver' => 'mysql',
            'url' => env('DATABASE_URL'),
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => env('DB_DATABASE', 'forge'),
            'username' => env('DB_USERNAME', 'forge'),
            'password' => env('DB_PASSWORD', ''),
            'unix_socket' => env('DB_SOCKET', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'prefix_indexes' => true,
            'strict' => true,
            'engine' => null,
            'options' => extension_loaded('pdo_mysql') ? array_filter([
                PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8, time_zone = 'UTC'",
                PDO::ATTR_EMULATE_PREPARES => env('DB_EMULATE_PREPARES', false),
            ]) : [],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Migration Repository Table
    |--------------------------------------------------------------------------
    |
    | This table keeps track of all the migrations that have already run for
    | your application. Using this information, we can determine which of
    | the migrations on disk haven't actually been run in the database.
    |
    */

    'migrations' => 'migrations',

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
            'prefix' => '',
        ],

        'default' => $redis,
        'ganesha' => $redis,
        'prometheus' => $redisPrometheus,
        'cache' => $redis,
        'hsm' => $redisHSM,
    ],
];
