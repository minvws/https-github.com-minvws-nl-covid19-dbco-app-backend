<?php

namespace MinVWS\DBCO\Metrics\Commands;

use MinVWS\DBCO\Metrics\Services\IntakeExportService;
use MinVWS\Metrics\Commands\AbstractUploadMetricsCommand;
use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Upload metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
class UploadIntakesCommand extends AbstractUploadMetricsCommand
{
    protected static $defaultName = 'intakes:upload';

    protected IntakeExportService $exportService;

    /**
     * Constructor.
     *
     * @param ExportService $exportService
     */
    public function __construct(IntakeExportService $exportService)
    {
        parent::__construct();
        $this->exportService = $exportService;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Upload intakes')
            ->setHelp('Can be used to (re-)upload exported intakes')
            ->addArgument('exportUuid', InputArgument::REQUIRED, 'Export identifier');
    }

    protected function getOutputMessage(): string
    {
        return 'Uploading intakes...';
    }
}
