<?php
declare(strict_types=1);

use function DI\env;
use Monolog\Logger;
use Psr\Container\ContainerInterface;

$debug = filter_var(getenv('DEBUG'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;
$securityModuleNonceUsePhpRandomBytes = filter_var(getenv('SECURITY_MODULE_NONCE_USE_PHP_RANDOM_BYTES'), FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? false;

return [
    'errorHandler.displayErrorDetails' => $debug,
    'errorHandler.logErrors' => true,
    'errorHandler.logErrorDetails' => $debug,

    'logger.name' => 'api',
    'logger.path' => 'php://stdout',
    'logger.level' => $debug ? Logger::DEBUG : Logger::INFO,

    'db' => [
        'type' => DI\env('DB_TYPE'),
        'host' => DI\env('DB_HOST'),
        'username' => DI\env('DB_USERNAME'),
        'database' => DI\env('DB_DATABASE'),
        'password' => DI\env('DB_PASSWORD'),
        'tns' => DI\env('DB_TNS', null)
    ],

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

    'privateAPI.client' => [
        'base_uri' => env('PRIVATE_API_BASE_URI')
    ],
    'privateAPI.jwtSecret' => env('PRIVATE_API_JWT_SECRET'),

    'securityModule.nonce.usePhpRandomBytes' => $securityModuleNonceUsePhpRandomBytes,
    'securityModule.storeKey.timeZone' => 'Europe/Amsterdam',
    'securityModule.storeKey.maxDays' => 14, // max days to store earlier keys for unsealing

    'metrics.export.basePath' => env('METRICS_EXPORT_BASE_PATH', '/tmp'),
    'metrics.export.filenameTemplate' => getenv('APP_ENV') . '_[timestamp].csv',
    'metrics.export.filenameTimestampFormat' => 'YmdHis',
    'metrics.export.fields' => [
        'id', 'event', 'date', 'ts_delta', 'actor', 'pseudo_id', 'vrregioncode',
        'date_of_symptom_onset', 'contact_pseudo_id', 'category', 'pct_complete',
        'fields', 'hpzone_id'
    ],
    'metrics.export.labels' => [
        'id', 'event', 'date', 'ts_delta', 'actor', 'pseudo_id', 'vrregioncode',
        'date_of_symptom_onset', 'contact_pseudo_id', 'category', 'pct_complete',
        'fields', 'hpzone_id'
    ],
    'metrics.sftp.hostname' => env('METRICS_SFTP_HOSTNAME', ''),
    'metrics.sftp.username' => env('METRICS_SFTP_USERNAME', ''),
    'metrics.sftp.privateKey' => env('METRICS_SFTP_PRIVATE_KEY', ''),
    'metrics.sftp.passphrase' => env('METRICS_SFTP_PASSPHRASE', null),
    'metrics.sftp.uploadPath' => env('METRICS_SFTP_UPLOAD_PATH', ''),
];
