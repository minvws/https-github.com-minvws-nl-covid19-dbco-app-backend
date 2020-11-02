<?php
declare(strict_types=1);

use DBCO\PrivateAPI\Application\Helpers\JWTConfigHelper;
use DBCO\PrivateAPI\Application\Helpers\SecureTokenGenerator;
use DBCO\PrivateAPI\Application\Helpers\TokenGenerator;
use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Predis\Client as PredisClient;
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
            TokenGenerator::class =>
                autowire(SecureTokenGenerator::class)
                    ->constructorParameter('allowedChars', get('pairingCode.allowedChars'))
                    ->constructorParameter('length', get('pairingCode.length')),
            JWTConfigHelper::class =>
                autowire(JWTConfigHelper::class)->constructor(get('jwt')),
            PredisClient::class => autowire(PredisClient::class)->constructor(get('redis'))
        ]
    );
};
