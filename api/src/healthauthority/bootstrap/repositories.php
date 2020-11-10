<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Repositories\CaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\ClientRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\DbCaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\RedisClientRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\StubCaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\StubGeneralTaskRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\StubQuestionnaireRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\GeneralTaskRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\QuestionnaireRepository;

use DI\Container;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        QuestionnaireRepository::class => autowire(StubQuestionnaireRepository::class),
        GeneralTaskRepository::class => autowire(StubGeneralTaskRepository::class),
        CaseRepository::class => function (Container $c) {
            $useStubs = $c->get('useStubs');
            if ($useStubs) {
                return $c->get(StubCaseRepository::class);
            } else {
                return $c->get(DbCaseRepository::class);
            }
        },
        ClientRepository::class => autowire(RedisClientRepository::class)
    ]);
};
