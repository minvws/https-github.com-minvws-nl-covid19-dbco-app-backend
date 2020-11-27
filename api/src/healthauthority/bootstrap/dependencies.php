<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Helpers\EncryptionHelper;
use DBCO\Shared\Application\Managers\DbTransactionManager;
use DBCO\Shared\Application\Managers\TransactionManager;
use DI\ContainerBuilder;
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
            EncryptionHelper::class =>
                autowire(EncryptionHelper::class)
                    ->constructor(
                        get('encryption.generalKeyPair')
                    ),
            'privateAPIGuzzleClient' =>
                autowire(GuzzleHttp\Client::class)
                    ->constructor(get('privateAPI.client'))
        ]
    );
};
