<?php

namespace MinVWS\Metrics\Commands;

use Exception;
use Illuminate\Support\Str;
use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use MinVWS\Metrics\Models\Export;

/**
 * Export metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
class ExportMetricsCommand extends AbstractExportMetricsCommand
{
    protected static $defaultName = 'metrics:export';

    /**
     * @var ExportService
     */
    protected ExportService $exportService;

    /**
     * Constructor.
     *
     * @param ExportService $exportService
     */
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
            ->setDescription('Export metrics')
            ->setHelp('Can be used to export metrics for certain events')
            ->addArgument('exportUuid', InputArgument::OPTIONAL, 'Export UUID, can be used to re-export an existing set of exported metrics', null)
            ->addOption('upload', 'u', InputOption::VALUE_NONE, 'Upload after export')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of events to export', null);
    }

    protected function getOutputMessage(): string
    {
        return 'Uploading metrics...';
    }
}
