<?php
declare(strict_types=1);

use DBCO\HealthAuthorityAPI\Application\Repositories\ApiCaseExportRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseExportRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\CaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\ClientRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\DbCaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\DbClientRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\DbQuestionnaireRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\StubCaseRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\StubGeneralTaskRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\GeneralTaskRepository;
use DBCO\HealthAuthorityAPI\Application\Repositories\QuestionnaireRepository;

use DI\ContainerBuilder;
use MinVWS\Metrics\Repositories\CsvExportRepository;
use MinVWS\Metrics\Repositories\DbStorageRepository;
use MinVWS\Metrics\Repositories\ExportRepository;
use MinVWS\Metrics\Repositories\SftpUploadRepository;
use MinVWS\Metrics\Repositories\StorageRepository;
use MinVWS\Metrics\Repositories\UploadRepository;
use function DI\autowire;
use function DI\get;

return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        QuestionnaireRepository::class => autowire(DbQuestionnaireRepository::class),
        GeneralTaskRepository::class => autowire(StubGeneralTaskRepository::class),
        CaseRepository::class => autowire(DbCaseRepository::class),
        ClientRepository::class => autowire(DbClientRepository::class),
        CaseExportRepository::class =>
            autowire(ApiCaseExportRepository::class)
                ->constructorParameter('client', get('privateAPIGuzzleClient'))
                ->constructorParameter('jwtSecret', get('privateAPI.jwtSecret')),
        StorageRepository::class => autowire(DbStorageRepository::class),
        ExportRepository::class =>
            autowire(CsvExportRepository::class)
                ->constructorParameter('fields', get('metrics.exportFields'))
                ->constructorParameter('labels', get('metrics.exportLabels')),
        UploadRepository::class => autowire(SftpUploadRepository::class)
    ]);
};
