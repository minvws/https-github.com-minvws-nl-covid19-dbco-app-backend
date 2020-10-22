<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Services\QuestionnaireService;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DI\ContainerBuilder;
use function DI\autowire;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        QuestionnaireService::class => autowire(QuestionnaireService::class),
        CaseService::class => autowire(CaseService::class)
    ]);
};
