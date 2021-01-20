<?php
declare(strict_types=1);

use DBCO\Bridge\Application\Services\QuestionnaireService;

use DBCO\Bridge\Application\Services\TaskService;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        QuestionnaireService::class => autowire(QuestionnaireService::class),
        TaskService::class => autowire(TaskService::class),
    ]);
};