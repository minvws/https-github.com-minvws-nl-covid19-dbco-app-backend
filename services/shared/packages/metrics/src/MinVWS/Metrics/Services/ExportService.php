<?php
namespace MinVWS\Metrics\Services;

use DateTimeImmutable;
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
     * @var string
     */
    private string $exportBasePath;

    /**
     * @var string
     */
    private string $exportFilenameTemplate;

    /**
     * @var string
     */
    private string $exportFilenameTimestampFormat;

    /**
     * @var StorageRepository
     */
    private StorageRepository $storageRepository;

    /**
     * @var ExportRepository
     */
    private ExportRepository $exportRepository;

    /**
     * @var UploadRepository
     */
    private UploadRepository $uploadRepository;

    /**
     * Constructor.
     *
     * @param string            $exportBasePath
     * @param string            $exportFilenameTemplate
     * @param string            $exportFilenameTimestampFormat
     * @param StorageRepository $storageRepository
     * @param ExportRepository  $exportRepository
     * @param UploadRepository  $uploadRepository
     */
    public function __construct(
        string $exportBasePath,
        string $exportFilenameTemplate,
        string $exportFilenameTimestampFormat,
        StorageRepository $storageRepository,
        ExportRepository $exportRepository,
        UploadRepository $uploadRepository
    ) {
        $this->exportBasePath = $exportBasePath;
        $this->exportFilenameTemplate = $exportFilenameTemplate;
        $this->exportFilenameTimestampFormat = $exportFilenameTimestampFormat;
        $this->storageRepository = $storageRepository;
        $this->exportRepository = $exportRepository;
        $this->uploadRepository = $uploadRepository;
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
     * Export the latest metrics to a file at the configured export path.
     *
     * @return Export
     */
    public function export(?string $exportUuid): Export
    {
        if ($exportUuid !== null) {
            $export = $this->storageRepository->getExport($exportUuid);
        } else {
            $export = new Export(Uuid::uuid4(), Export::STATUS_INITIAL, new DateTimeImmutable());
            $this->storageRepository->createExport($export);
        }

        $exportedAt = new DateTimeImmutable();

        $filename = str_replace(
            [
                '[uuid]',
                '[timestamp]'
            ],
            [
                $export->uuid,
                $exportedAt->format($this->exportFilenameTimestampFormat)
            ],
            $this->exportFilenameTemplate
        );

        $path = $this->exportBasePath . '/' . $filename;

        $export->eventCount = 0;
        $handle = $this->exportRepository->openFile($path, $export);
        $this->storageRepository->iterateEventsForExport($export->uuid, function (Event $event) use ($handle, $export) {
            $this->exportRepository->addEventToFile($event, $handle);
            $export->eventCount += 1;
        });
        $this->exportRepository->closeFile($handle);

        $export->status = Export::STATUS_EXPORTED;
        $export->exportedAt = $exportedAt;
        $export->filename = $filename;
        $this->storageRepository->updateExport($export, ['status', 'exportedAt', 'filename']);

        return $export;
    }

    /**
     * Upload export.
     *
     * @param Export $export Export.
     */
    public function upload(Export $export)
    {
        $path = $this->exportBasePath . '/' . $export->filename;
        $this->uploadRepository->uploadFile($path, $export);
        $export->status = Export::STATUS_UPLOADED;
        $export->uploadedAt = new DateTimeImmutable();
        $this->storageRepository->updateExport($export, ['status', 'uploadedAt']);
    }
}
