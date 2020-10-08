<?php
declare(strict_types=1);

use App\Application\Helpers\RandomKeyGeneratorInterface;

use DBCO\Application\Repositories\DbPairingRepository;
use DBCO\Application\Repositories\PairingRepository;
use DBCO\Application\Repositories\CaseRepository;
use DBCO\Application\Repositories\DbCaseRepository;

use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        CaseRepository::class => autowire(DbCaseRepository::class),
        PairingRepository::class => autowire(DbPairingRepository::class)
    ]);
};
