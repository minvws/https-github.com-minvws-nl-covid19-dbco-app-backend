<?php
declare(strict_types=1);

use DBCO\Worker\Application\Repositories\ApiHealthAuthorityPairingRepository;
use DBCO\Worker\Application\Repositories\Source;
use DBCO\Worker\Application\Repositories\GeneralTaskCacheRepository;
use DBCO\Worker\Application\Repositories\GeneralTaskGetRepository;
use DBCO\Worker\Application\Repositories\HealthAuthorityPairingRepository;
use DBCO\Worker\Application\Repositories\QuestionnaireCacheRepository;
use DBCO\Worker\Application\Repositories\QuestionnaireGetRepository;
use DBCO\Worker\Application\Repositories\HAGetRepository;
use DBCO\Worker\Application\Repositories\RedisCacheRepository;

use DBCO\Worker\Application\Repositories\RedisSource;
use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        HAGetRepository::class =>
            autowire(HAGetRepository::class)
                ->constructorParameter('client', get('healthAuthorityGuzzleClient')),
        RedisCacheRepository::class => autowire(RedisCacheRepository::class),

        QuestionnaireGetRepository::class => get(HAGetRepository::class),
        QuestionnaireCacheRepository::class => get(RedisCacheRepository::class),

        GeneralTaskGetRepository::class => get(HAGetRepository::class),
        GeneralTaskCacheRepository::class => get(RedisCacheRepository::class),

        Source::class => autowire(RedisSource::class),
        HealthAuthorityPairingRepository::class =>
            autowire(ApiHealthAuthorityPairingRepository::class)
                ->constructorParameter('client', get('healthAuthorityGuzzleClient'))
    ]);
};
