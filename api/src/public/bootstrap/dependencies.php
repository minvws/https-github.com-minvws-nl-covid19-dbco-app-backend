<?php
declare(strict_types=1);

use App\Application\Helpers\KeyGenerator;
use App\Application\Helpers\SecureKeyGenerator;
use DBCO\Application\Managers\DbTransactionManager;
use DBCO\Application\Managers\TransactionManager;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Predis\Client as PredisClient;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions(
        [
            'logger.handlers' => [
                autowire(StreamHandler::class)->constructor(get('logger.path'), get('logger.level'))
            ],
            'logger.processors' => [
                autowire(UidProcessor::class)
            ],
            LoggerInterface::class =>
                autowire(Logger::class)
                    ->constructor(
                        get('logger.name'),
                        get('logger.handlers'),
                        get('logger.processors')
                    ),
            PDO::class => function (ContainerInterface $c) {
                $settings = $c->get('db');
                $host = $settings['host'];
                $dbname = $settings['database'];
                $username = $settings['username'];
                $password = $settings['password'];
                $driver = $settings['driver'];
                $dsn = "$driver:host=$host;dbname=$dbname";
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
                return $pdo;
            },
            PredisClient::class => autowire(PredisClient::class)->constructor(get('redis')),
            TransactionManager::class => autowire(DbTransactionManager::class),
            KeyGenerator::class => autowire(SecureKeyGenerator::class)->constructorParameter('length', get('signingKey.length'))
        ]
    );
};
