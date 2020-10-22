<?php
declare(strict_types=1);

use DBCO\PrivateAPI\Application\Repositories\PairingRequestRepository;
use DBCO\PrivateAPI\Application\Repositories\RedisPairingRequestRepository;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PairingRequestRepository::class => autowire(RedisPairingRequestRepository::class)
    ]);
};
