<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Services\QuestionnaireService;
use DBCO\HealthAuthorityAPI\Application\Services\CaseService;
use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use DI\ContainerBuilder;
use MinVWS\Metrics\Services\ExportService;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        QuestionnaireService::class => autowire(QuestionnaireService::class),
        CaseService::class => autowire(CaseService::class),
        SecurityService::class => autowire(SecurityService::class)
            ->constructorParameter('storeKeyTimeZone', get('securityModule.storeKey.timeZone'))
            ->constructorParameter('storeKeyMaxDays', get('securityModule.storeKey.maxDays')),
        ExportService::class => autowire(ExportService::class)
            ->constructorParameter('exportBasePath', get('metrics.export.basePath'))
            ->constructorParameter('exportFilenameTemplate', get('metrics.export.filenameTemplate'))
            ->constructorParameter('exportFilenameTimestampFormat', get('metrics.export.filenameTimestampFormat'))
    ]);
};
