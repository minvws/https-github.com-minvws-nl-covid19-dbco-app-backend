<?php
declare(strict_types=1);

use DBCO\PublicAPI\Application\Services\ConfigService;
use DBCO\PublicAPI\Application\Services\PairingService;
use DBCO\PublicAPI\Application\Services\CaseService;
use DBCO\PublicAPI\Application\Services\QuestionnaireService;
use DI\ContainerBuilder;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        PairingService::class => autowire(PairingService::class),
        CaseService::class => autowire(CaseService::class),
        QuestionnaireService::class => autowire(QuestionnaireService::class),
        ConfigService::class => autowire(ConfigService::class)
    ]);
};
