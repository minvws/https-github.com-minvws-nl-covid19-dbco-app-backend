<?php
declare(strict_types=1);

use DBCO\Shared\Application\Managers\DbTransactionManager;
use DBCO\Shared\Application\Managers\TransactionManager;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
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

                if ($settings['type'] === 'postgres') {
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
            TransactionManager::class => autowire(DbTransactionManager::class)
        ]
    );
};
