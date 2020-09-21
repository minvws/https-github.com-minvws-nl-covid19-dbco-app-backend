<?php
declare(strict_types=1);

use App\Application\Managers\DbTransactionManager;
use App\Application\Managers\TransactionManager;

use App\Application\Services\ExportManifestService;
use App\Application\Services\ExportTEKsService;
use App\Application\Services\ImportStaticFileService;
use App\Application\Signers\CMSSigner;
use App\Application\Signers\SHA256Signer;
use DI\ContainerBuilder;
use function DI\autowire;
use function DI\env;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use function DI\get;

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
            },
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
    );

    $containerBuilder->addDefinitions([
        ExampleService::class =>
            autowire(ExampleService::class)
                ->constructorParameter('logger', get(LoggerInterface::class)) // not really necessary
    ]);

    $containerBuilder->addDefinitions([
        TransactionManager::class => autowire(DbTransactionManager::class)
    ]);

};
