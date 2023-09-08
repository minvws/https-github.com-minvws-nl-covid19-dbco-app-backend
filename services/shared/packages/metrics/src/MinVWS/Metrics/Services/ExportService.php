<?php

namespace MinVWS\Metrics\Services;

use DateTimeImmutable;
use DateTimeZone;
use Exception;
use Illuminate\Support\Str;
use MinVWS\Metrics\Helpers\CMSHelper;
use MinVWS\Metrics\Models\ExportConfig;
use MinVWS\Metrics\Models\Event;
use MinVWS\Metrics\Models\Export;
use MinVWS\Metrics\Repositories\ExportRepository;
use MinVWS\Metrics\Repositories\StorageRepository;
use MinVWS\Metrics\Repositories\UploadRepository;
use Ramsey\Uuid\Uuid;

/**
 * Export service.
 *
 * @package MinVWS\Metrics\Services
 */
class ExportService
{
    /**
     * @var ExportConfig
     */
    protected ExportConfig $config;

    /**
     * @var StorageRepository
     */
    protected StorageRepository $storageRepository;

    /**
     * @var ExportRepository
     */
    protected ExportRepository $exportRepository;

    /**
     * @var UploadRepository
     */
    protected UploadRepository $uploadRepository;

    /**
     * @var CMSHelper
     */
    protected CMSHelper $cmsHelper;

    /**
     * Constructor.
     *
     * @param ExportConfig      $config
     * @param StorageRepository $storageRepository
     * @param ExportRepository  $exportRepository
     * @param UploadRepository  $uploadRepository
     * @param CMSHelper         $cmsHelper
     */
    public function __construct(
        ExportConfig $config,
        StorageRepository $storageRepository,
        ExportRepository $exportRepository,
        UploadRepository $uploadRepository,
        CMSHelper $cmsHelper
    ) {
        $this->config = $config;
        $this->storageRepository = $storageRepository;
        $this->exportRepository = $exportRepository;
        $this->uploadRepository = $uploadRepository;
        $this->cmsHelper = $cmsHelper;
    }

    /**
     * Count exports with the given status.
     *
     * @param array $status
     *
     * @return int
     */
    public function countExports(array $status)
    {
        return $this->storageRepository->countExports($status);
    }

    /**
     * List exports with the given status.
     *
     * @param int   $limit
     * @param int   $offset
     * @param array $status
     */
    public function listExports(int $limit, int $offset, array $status)
    {
        return $this->storageRepository->listExports($limit, $offset, $status);
    }

    /**
     * Retrieve export.
     *
     * @param string $exportUuid
     *
     * @return Export|null
     */
    public function getExport(string $exportUuid): ?Export
    {
        return $this->storageRepository->getExport($exportUuid);
    }

    /**
     * Returns the export filename.
     *
     * @param Export $export
     *
     * @return string
     */
    public function getFilenameForExport(Export $export): string
    {
        return str_replace(
            [
                '[uuid]',
                '[timestamp]'
            ],
            [
                $export->uuid,
                $export->exportedAt->format($this->config->filenameTimestampFormat)
            ],
            $this->config->filenameTemplate
        );
    }

    /**
     * Export data.
     *
     * @param Export $export
     *
     * @return string
     */
    protected function exportData(Export $export): string
    {
        $handle = fopen('php://memory', 'w');

        $this->exportRepository->addHeaderToStream($export, $handle);

        $export->itemCount = 0;
        $this->storageRepository->iterateForExport($export->uuid, function ($event) use ($handle, $export) {
            $this->exportRepository->addObjectToStream($event, $handle);
            $export->itemCount += 1;
        });

        $this->exportRepository->addFooterToStream($export, $handle);

        rewind($handle);
        $data =  stream_get_contents($handle);

        fclose($handle);

        return $data;
    }

    /**
     * Store exported events on disk.
     *
     * @param string $filename
     * @param string $data
     *
     * @throws Exception
     */
    protected function storeExportData(string $filename, string $data)
    {
        $path = $this->config->basePath . '/' . $filename;

        if ($this->config->encryption->isEnabled) {
            $data = $this->cmsHelper->encrypt($data, $this->config->encryption);
        }

        file_put_contents($path, $data);

        if ($this->config->signature->isEnabled) {
            $signaturePath = $path . '.sig';
            $signature = $this->cmsHelper->sign($data, $this->config->signature);
            file_put_contents($signaturePath, $signature);
        }
    }

    /**
     * Export the latest metrics to a file at the configured export path.
     *
     * @param string|null $exportUuid Existing export UUID if you want to re-export an existing export, or null.
     * @param int|null    $limit      Limit the number of events exported.
     *
     * @return Export
     *
     * @throws Exception
     */
    public function export(?string $exportUuid = null, ?int $limit = null): Export
    {
        if ($exportUuid !== null) {
            $export = $this->storageRepository->getExport($exportUuid);
        } else {
            $export = new Export(Uuid::uuid4(), Export::STATUS_INITIAL, new DateTimeImmutable('now', new DateTimeZone('UTC')));
            $this->storageRepository->createExport($export, $limit);
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
     * Upload export.
     *
     * @param Export $export Export.
     *
     * @throws Exception
     */
    public function upload(Export $export)
    {
        $path = $this->config->basePath . '/' . $export->filename;
        $this->uploadRepository->uploadFile($path, $export);
        $export->status = Export::STATUS_UPLOADED;
        $export->uploadedAt = new DateTimeImmutable('now', new DateTimeZone('UTC'));
        $this->storageRepository->updateExport($export, ['status', 'uploadedAt']);
    }
}
