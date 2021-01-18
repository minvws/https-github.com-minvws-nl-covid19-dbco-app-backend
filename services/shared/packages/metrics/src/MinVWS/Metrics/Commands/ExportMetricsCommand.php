<?php
namespace MinVWS\Metrics\Commands;

use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Export metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
class ExportMetricsCommand extends Command
{
    protected static $defaultName = 'metrics:export';

    /**
     * @var ExportService
     */
    private ExportService $exportService;

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
            ->addOption('upload', 'u', InputOption::VALUE_NONE, 'Upload after export')
            ->addArgument('exportUuid', InputArgument::OPTIONAL, 'Export UUID, can be used to re-export an existing set of exported metrics', null);
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->write('Exporting metrics...');
        $exportUuid = $input->getArgument('exportUuid', null);
        $export = $this->exportService->export($exportUuid);
        $output->writeln(' [OK]');
        $output->writeln('');
        $output->writeln(sprintf('Export UUID     : %s', $export->uuid));
        $output->writeln(sprintf('Export filename : %s', $export->filename));
        $output->writeln(sprintf('Events          : %d', $export->eventCount));

        if ($input->getOption('upload')) {
            $output->writeln('');
            $output->write('Uploading metrics...');
            $this->exportService->upload($export);
            $output->writeln(' [OK]');
        }

        return Command::SUCCESS;
    }
}
