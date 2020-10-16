<?php
declare(strict_types=1);

use App\Application\Repositories\GeneralTaskCacheRepository;
use App\Application\Repositories\GeneralTaskGetRepository;
use App\Application\Repositories\QuestionnaireCacheRepository;
use App\Application\Repositories\QuestionnaireGetRepository;
use App\Application\Repositories\HAGetRepository;
use App\Application\Repositories\RedisCacheRepository;

use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        HAGetRepository::class => autowire(HAGetRepository::class)->constructorParameter('client', get('healthAuthorityGuzzleClient')),
        RedisCacheRepository::class => autowire(RedisCacheRepository::class),

        QuestionnaireGetRepository::class => get(HAGetRepository::class),
        QuestionnaireCacheRepository::class => get(RedisCacheRepository::class),

        GeneralTaskGetRepository::class => get(HAGetRepository::class),
        GeneralTaskCacheRepository::class => get(RedisCacheRepository::class),
    ]);
};
