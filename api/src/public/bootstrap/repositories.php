<?php
declare(strict_types=1);

use App\Application\Repositories\CaseTaskRepository;
use App\Application\Repositories\StubCaseTaskRepository;
use App\Application\Repositories\RedisGeneralTaskRepository;
use App\Application\Repositories\RedisQuestionnaireRepository;
use App\Application\Repositories\GeneralTaskRepository;
use App\Application\Repositories\QuestionnaireRepository;
use DBCO\Application\Repositories\DbPairingRepository;
use DBCO\Application\Repositories\PairingRepository;
use DBCO\Application\Repositories\CaseRepository;
use DBCO\Application\Repositories\DbCaseRepository;

use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        CaseRepository::class => autowire(DbCaseRepository::class),
        PairingRepository::class => autowire(DbPairingRepository::class),
        QuestionnaireRepository::class => autowire(RedisQuestionnaireRepository::class),
        GeneralTaskRepository::class => autowire(RedisGeneralTaskRepository::class),
        CaseTaskRepository::class => autowire(StubCaseTaskRepository::class),
    ]);
};
