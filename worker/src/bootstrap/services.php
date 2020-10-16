<?php
declare(strict_types=1);

use App\Application\Services\QuestionnaireService;

use App\Application\Services\TaskService;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        QuestionnaireService::class => autowire(QuestionnaireService::class),
        TaskService::class => autowire(TaskService::class),
    ]);
};