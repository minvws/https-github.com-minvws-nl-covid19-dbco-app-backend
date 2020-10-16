<?php
declare(strict_types=1);

use App\Application\Repositories\StubCaseRepository;
use App\Application\Repositories\StubGeneralTaskRepository;
use App\Application\Repositories\StubQuestionnaireRepository;
use App\Application\Repositories\GeneralTaskRepository;
use App\Application\Repositories\QuestionnaireRepository;
use DBCO\Application\Repositories\DbPairingRepository;
use DBCO\Application\Repositories\PairingRepository;
use DBCO\Application\Repositories\DbCaseRepository;

use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        DBCO\Application\Repositories\CaseRepository::class => autowire(DbCaseRepository::class),
        PairingRepository::class => autowire(DbPairingRepository::class),
        QuestionnaireRepository::class => autowire(StubQuestionnaireRepository::class),
        GeneralTaskRepository::class => autowire(StubGeneralTaskRepository::class),
        App\Application\Repositories\CaseRepository::class => autowire(StubCaseRepository::class),
    ]);
};
