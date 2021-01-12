<?php
namespace MinVWS\Metrics\Services;

use DateTimeImmutable;
use MinVWS\Metrics\Events\Event;
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
     * Export the latest metrics to a file at the configured export path.
     *
     * @return Export
     */
    public function export(): Export
    {
        $export = new Export(Uuid::uuid4(), Export::STATUS_INITIAL, new DateTimeImmutable());
        $this->storageRepository->createExport($export);

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

        $handle = $this->exportRepository->openFile($path, $export);
        $this->storageRepository->iterateEventsForExport($export->uuid, function (Event $event) use ($handle) {
            $this->exportRepository->addEventToFile($event, $handle);
        });
        $this->exportRepository->closeFile($handle);

        $export->filename = $filename;

        $export->exportedAt = $exportedAt;
        $this->storageRepository->updateExport($export, ['exportedAt']);

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
        $export->uploadedAt = new DateTimeImmutable();
        $this->storageRepository->updateExport($export, ['uploadedAt']);
    }
}
