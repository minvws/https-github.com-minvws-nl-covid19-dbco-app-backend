<?php

namespace MinVWS\Metrics\Commands;

use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Upload metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
class UploadMetricsCommand extends AbstractUploadMetricsCommand
{
    protected static $defaultName = 'metrics:upload';

    protected ExportService $exportService;

    public function __construct(ExportService $exportService)
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
            ->setDescription('Upload metrics')
            ->setHelp('Can be used to (re-)upload exported metrics')
            ->addArgument('exportUuid', InputArgument::REQUIRED, 'Export identifier');
    }

    protected function getOutputMessage(): string
    {
        return 'Uploading metrics...';
    }
}
