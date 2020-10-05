<?php
declare(strict_types=1);

use App\Application\Repositories\DbPairingRepository;
use App\Application\Repositories\PairingRepository;
use App\Application\Repositories\CaseRepository;
use App\Application\Repositories\DbCaseRepository;
use App\Application\Helpers\RandomKeyGeneratorInterface;

use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        CaseRepository::class => autowire(DbCaseRepository::class),
        PairingRepository::class => autowire(DbPairingRepository::class)
    ]);
};
