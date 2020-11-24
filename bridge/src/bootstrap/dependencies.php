<?php
declare(strict_types=1);

use DBCO\Bridge\Application\Commands\LaneCommand;
use DBCO\Bridge\Application\Commands\StatusCommand;
use DBCO\Bridge\Application\Services\LaneService;
use DI\ContainerBuilder;
use function DI\autowire;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;

use Predis\Client as PredisClient;

use Psr\Log\LoggerInterface;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions(
        [
            'logger.handlers' => [
                autowire(StreamHandler::class)
                    ->constructor(get('logger.path'), get('logger.level'))
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
            PredisClient::class =>
                autowire(PredisClient::class)->constructor(get('redis')),
            'healthAuthorityGuzzleClient' =>
                autowire(GuzzleHttp\Client::class)->constructor(get('healthAuthorityAPI')),
            StatusCommand::class => autowire(StatusCommand::class)
                ->constructorParameter(
                    'healthAuthorityGuzzleClient',
                    get('healthAuthorityGuzzleClient')
                )
        ]
    );

    $lanes = require(__DIR__ . '/lanes.php');
    foreach ($lanes as $lane) {
        $name = $lane['name'];

        $service =
            autowire(LaneService::class)
                ->constructorParameter('source', get("lane.{$name}.source"))
                ->constructorParameter('destination', get("lane.{$name}.destination"));

        $command =
            autowire(LaneCommand::class)
                ->constructorParameter('name', $name)
                ->constructorParameter('description', $lane['description'])
                ->constructorParameter('laneService', get("lane.{$name}.service"));

        $containerBuilder->addDefinitions([
            "lane.{$name}.source" => $lane['source'],
            "lane.{$name}.destination" => $lane['destination'],
            "lane.{$name}.service" => $service,
            "lane.{$name}.command" => $command
        ]);
    }
};
