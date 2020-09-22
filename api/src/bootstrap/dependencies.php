<?php
declare(strict_types=1);

use App\Application\Managers\DbTransactionManager;
use App\Application\Managers\TransactionManager;

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use App\Application\Helpers\RandomKeyGeneratorInterface;
use App\Application\Helpers\RandomKeyGenerator;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions(
        [
            LoggerInterface::class => function (ContainerInterface $c) {
                $settings = $c->get('settings');

                $loggerSettings = $settings['logger'];
                $logger = new Logger($loggerSettings['name']);

                $processor = new UidProcessor();
                $logger->pushProcessor($processor);

                $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
                $logger->pushHandler($handler);

                return $logger;
            }
        ],
        [
            'PDO' => function (ContainerInterface $c) {
                $settings = $c->get('settings')['db'];
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
        ],
        [
            TransactionManager::class => autowire(DbTransactionManager::class),
        ],
        [
            RandomKeyGeneratorInterface::class => function (ContainerInterface $c) {
                $options = $c->get('settings')['randomKeyGenerator'];
                return new RandomKeyGenerator($options);
            }
        ]
    );
};
