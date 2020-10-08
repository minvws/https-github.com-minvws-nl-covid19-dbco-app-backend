<?php
declare(strict_types=1);

use App\Application\Services\PairingService;

use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PairingService::class => autowire(PairingService::class)
    ]);
};
