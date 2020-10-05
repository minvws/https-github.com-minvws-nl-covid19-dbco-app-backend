<?php
declare(strict_types=1);

use App\Application\Services\CaseService;

use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        CaseService::class => autowire(CaseService::class)->constructorParameter('pairingCodeTimeToLive', get('pairingCode.timeToLive'))
    ]);
};
