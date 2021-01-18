<?php
declare(strict_types=1);

use DBCO\PublicAPI\Application\Helpers\KeyGenerator;
use DBCO\PublicAPI\Application\Helpers\SecureKeyGenerator;
use DI\ContainerBuilder;
use MinVWS\HealthCheck\Checks\PredisHealthCheck;
use MinVWS\HealthCheck\HealthChecker;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Predis\Client as PredisClient;
use Psr\Log\LoggerInterface;
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

            PredisClient::class =>
                autowire(PredisClient::class)
                    ->constructor(get('redis.parameters'), get('redis.options')),

            KeyGenerator::class => autowire(SecureKeyGenerator::class)
                ->constructorParameter('length', get('signingKey.length')),

            HealthChecker::class =>
                autowire(HealthChecker::class)
                    ->method('addHealthCheck', autowire(PredisHealthCheck::class))
        ]
    );
};
