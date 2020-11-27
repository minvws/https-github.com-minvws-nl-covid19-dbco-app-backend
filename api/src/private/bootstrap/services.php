<?php
declare(strict_types=1);

use DBCO\PrivateAPI\Application\Services\CaseService;

use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        CaseService::class => autowire(CaseService::class)
            ->constructorParameter('pairingCodeExpiresDelta', get('pairingCode.expiresDelta'))
            ->constructorParameter('pairingCodeExpiredWarningDelta', get('pairingCode.expiredWarningDelta'))
            ->constructorParameter('pairingCodeBlockedDelta', get('pairingCode.blockedDelta'))
    ]);
};
