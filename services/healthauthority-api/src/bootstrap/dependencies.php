<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Security\ProxySecurityCache;
use DBCO\HealthAuthorityAPI\Application\Security\RedisSecurityCache;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityCache;
use DBCO\HealthAuthorityAPI\Application\Security\HSMSecurityModule;
use DBCO\HealthAuthorityAPI\Application\Security\SecurityModule;
use DBCO\Shared\Application\Managers\DbTransactionManager;
use DBCO\Shared\Application\Managers\TransactionManager;
use DBCO\Shared\Application\Metrics\Transformers\EventTransformer;
use DI\ContainerBuilder;
use MinVWS\HealthCheck\Checks\GuzzleHealthCheck;
use MinVWS\HealthCheck\Checks\PDOHealthCheck;
use MinVWS\HealthCheck\Checks\PredisHealthCheck;
use MinVWS\HealthCheck\HealthChecker;
use MinVWS\Metrics\Transformers\EventTransformer as EventTransformerInterface;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Predis\Client as PredisClient;
use Psr\Log\NullLogger;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $isTestEnvironment = getenv('APP_ENV') === 'test';

    $containerBuilder->addDefinitions(
        [
            'logger.handlers' => [
                autowire(StreamHandler::class)
                    ->constructor(get('logger.path'), get('logger.level'))
            ],
            'logger.processors' => [
                autowire(UidProcessor::class)
            ],
            'logger.default' =>
                autowire(Logger::class)
                    ->constructor(
                        get('logger.name'),
                        get('logger.handlers'),
                        get('logger.processors')
                    ),
            LoggerInterface::class =>
                $isTestEnvironment ? autowire(NullLogger::class) : get('logger.default'),

            PDO::class => function (ContainerInterface $c) {
                $settings = $c->get('db');

                if ($settings['type'] === 'mysql') {
                    $host = $settings['host'];
                    $db = $settings['database'];
                    $dsn = "mysql:host=$host;dbname=$db";
                } else if ($settings['type'] === 'postgres') {
                    $host = $settings['host'];
                    $db = $settings['database'];
                    $dsn = "pgsql:host=$host;dbname=$db";
                } else { // oracle
                    if (!empty($settings['tns'])) {
                        $tns = $settings['tns'];
                        $dsn = "oci:dbname=$tns";
                    } else {
                        $host = $settings['host'];
                        $db = $settings['database'];
                        $dsn = "oci:dbname=//$host:1521/$db";
                    }
                }

                $username = $settings['username'];
                $password = $settings['password'];

                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);

                if ($settings['type'] === 'oracle') {
                    $db = $settings['database'];
                    $pdo->query('ALTER SESSION SET CURRENT_SCHEMA = ' . $db);
                }

                return $pdo;
            },
            PredisClient::class =>
                autowire(PredisClient::class)
                    ->constructor(get('redis.parameters'), get('redis.options')),
            TransactionManager::class => autowire(DbTransactionManager::class),
            SecurityCache::class => function (ContainerInterface $c) {
                return new ProxySecurityCache(new RedisSecurityCache($c->get(PredisClient::class)));
            },
            SecurityModule::class =>
                autowire(HSMSecurityModule::class)
                    ->constructorParameter('usePhpRandomBytesForNonce', get('securityModule.nonce.usePhpRandomBytes')),
            'privateAPIGuzzleClient' =>
                autowire(GuzzleHttp\Client::class)
                    ->constructor(get('privateAPI.client')),

            EventTransformerInterface::class => autowire(EventTransformer::class),

            HealthChecker::class =>
                autowire(HealthChecker::class)
                    ->method('addHealthCheck', 'redis', autowire(PredisHealthCheck::class))
                    ->method('addHealthCheck', 'mysql', autowire(PDOHealthCheck::class))
                    ->method('addHealthCheck',
                        'private-api',
                        autowire(GuzzleHealthCheck::class)
                            ->constructor(get('privateAPIGuzzleClient'), 'GET', 'ping')
                            ->method('setExpectedResponseBody', 'PONG')
                    )
        ]
    );
};
