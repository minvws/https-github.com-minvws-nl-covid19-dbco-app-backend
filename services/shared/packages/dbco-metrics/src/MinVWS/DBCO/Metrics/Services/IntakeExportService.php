<?php

namespace MinVWS\DBCO\Metrics\Services;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use MinVWS\Audit\AuditService;
use MinVWS\Audit\Helpers\AuditEventHelper;
use MinVWS\Audit\Helpers\PHPDocHelper;
use MinVWS\Audit\Models\AuditEvent;
use MinVWS\Audit\Models\AuditObject;
use MinVWS\DBCO\Metrics\Repositories\IntakeCsvExportRepository;
use MinVWS\DBCO\Metrics\Repositories\IntakeDbStorageRepository;
use MinVWS\Metrics\Helpers\CMSHelper;
use MinVWS\Metrics\Models\Export;
use MinVWS\Metrics\Models\ExportConfig;
use MinVWS\Metrics\Repositories\UploadRepository;
use MinVWS\Metrics\Services\ExportService;
use Ramsey\Uuid\Uuid;

/**
 * @package MinVWS\Metrics\Services
 */
class IntakeExportService extends ExportService
{
    protected AuditService $auditService;
    protected array $fields;

    /**
     * @param ExportConfig              $config
     * @param IntakeDbStorageRepository $storageRepository
     * @param IntakeCsvExportRepository $exportRepository
     * @param UploadRepository          $uploadRepository
     * @param CMSHelper                 $cmsHelper
     * @param AuditService              $auditService
     * @param array                     $fields
     */
    public function __construct(
        ExportConfig $config,
        IntakeDbStorageRepository $storageRepository,
        IntakeCsvExportRepository $exportRepository,
        UploadRepository $uploadRepository,
        CMSHelper $cmsHelper,
        AuditService $auditService,
        array $fields
    ) {
        $this->config = $config;
        $this->storageRepository = $storageRepository;
        $this->exportRepository = $exportRepository;
        $this->uploadRepository = $uploadRepository;
        $this->cmsHelper = $cmsHelper;
        $this->auditService = $auditService;
        $this->fields = $fields;
    }

    /**
     * @auditEventDescription Export intakes to RIVM.
     */
    public function export(?string $exportUuid = null, ?int $limit = null, ?string $type = null): Export
    {
        if ($exportUuid !== null) {
            $export = $this->storageRepository->getExport($exportUuid);
        } else {
            $export = new Export(Uuid::uuid4(), Export::STATUS_INITIAL, new DateTimeImmutable('now', new DateTimeZone('UTC')));
            $this->storageRepository->createIntakeExport($export, $limit, $type);
        }

        $export->status = Export::STATUS_EXPORTED;
        $export->exportedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $export->filename = $this->getFilenameForExport($export);

        $data = $this->exportData($export);
        $this->storeExportData($export->filename, $data);
        $this->storageRepository->updateExport($export, ['status', 'exportedAt', 'filename']);

        return $export;
    }

    /**
     * @inheritdoc
     */
    protected function exportData(Export $export): string
    {
        $handle = fopen('php://memory', 'w');

        $this->exportRepository->addHeaderToStream($export, $handle);

        $export->itemCount = 0;
        $intakes = [];
        $i = 1;
        $this->storageRepository->iterateForExport($export->uuid, function ($intake) use ($handle, $export, &$intakes, &$i) {
            $this->exportRepository->addObjectToStream($intake, $handle);
            $intakes[] = $intake;
            //Register an Audit log for each 100 intakes
            if ($i++ % 100 === 0) {
                $this->registerIntakeAudit($export->uuid, $intakes);
                $intakes = [];
                $i = 1;
            }
            $export->itemCount += 1;
        });

        $this->registerIntakeAudit($export->uuid, $intakes);

        $this->exportRepository->addFooterToStream($export, $handle);

        rewind($handle);
        $data =  stream_get_contents($handle);

        fclose($handle);

        return $data;
    }

    /**
     * Register an Audit log for the given intakes
     *
     * @throws Exception
     */
    public function registerIntakeAudit(?string $exportUuid, $intakes): void
    {
        $action = 'MinVWS\\DBCO\\Metrics\\Services\\IntakeExportService::export';

        /** TODO: Simplify as soon as this library is only used by projects with PHP version > 8 */
        $description = PHP_MAJOR_VERSION < 8
            ? PHPDocHelper::getTagAuditEventDescriptionByActionName($action)
            : AuditEventHelper::getAuditEventDescriptionByActionName($action);

        $this->auditService->registerEvent(
            AuditEvent::create($action, AuditEvent::ACTION_READ, $description)
                ->object(AuditObject::create('export-rivm', $exportUuid)->detail('fields', $this->getIntakeFields())),
            fn (AuditEvent $auditEvent) => $auditEvent->objects(array_map(fn ($i) => AuditObject::create('intake', $i->uuid), $intakes))
        );
        $this->auditService->finalizeEvent();
    }

    /**
     * Get export fields from config and convert to string list
     */
    private function getIntakeFields(): string
    {
        foreach ($this->fields as $fieldKey => $fieldValue) {
            if (is_array($fieldValue)) {
                foreach ($fieldValue as $subHeader) {
                    $fieldNames[] = $fieldKey . '.' . $subHeader;
                }
            } else {
                $fieldNames[] = $fieldValue;
            }
        }

        return implode(',', $fieldNames);
    }
}
