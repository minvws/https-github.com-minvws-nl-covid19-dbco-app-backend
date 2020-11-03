<?php
declare(strict_types=1);

use DBCO\PublicAPI\Application\Repositories\CaseRepository;
use DBCO\PublicAPI\Application\Repositories\ConfigRepository;
use DBCO\PublicAPI\Application\Repositories\RedisCaseRepository;
use DBCO\PublicAPI\Application\Repositories\RedisGeneralTaskRepository;
use DBCO\PublicAPI\Application\Repositories\RedisQuestionnaireRepository;
use DBCO\PublicAPI\Application\Repositories\GeneralTaskRepository;
use DBCO\PublicAPI\Application\Repositories\QuestionnaireRepository;
use DBCO\PublicAPI\Application\Repositories\RedisPairingRequestRepository;
use DBCO\PublicAPI\Application\Repositories\RedisPairingRepository;
use DBCO\PublicAPI\Application\Repositories\PairingRepository;
use DBCO\PublicAPI\Application\Repositories\PairingRequestRepository;
use DBCO\PublicAPI\Application\Repositories\SimpleConfigRepository;
use DBCO\PublicAPI\Application\Repositories\StubCaseRepository;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PairingRequestRepository::class => autowire(RedisPairingRequestRepository::class),
        PairingRepository::class => autowire(RedisPairingRepository::class),
        QuestionnaireRepository::class => autowire(RedisQuestionnaireRepository::class),
        GeneralTaskRepository::class => autowire(RedisGeneralTaskRepository::class),
        CaseRepository::class => autowire(StubCaseRepository::class),
        ConfigRepository::class => autowire(SimpleConfigRepository::class)
    ]);
};
