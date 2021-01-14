<?php
namespace MinVWS\Metrics\Commands;

use MinVWS\Metrics\Models\Export;
use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upload metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
class UploadMetricsCommand extends Command
{
    protected static $defaultName = 'metrics:upload';

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
            ->setDescription('Upload metrics')
            ->setHelp('Can be used to (re-)upload exported metrics')
            ->addArgument('exportUuid', InputArgument::REQUIRED, 'Export identifier');
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
        $output->write('Retrieving export...');
        $exportUuid = $input->getArgument('exportUuid');
        $export = $this->exportService->getExport($exportUuid);
        if ($export !== null && $export->status !== Export::STATUS_INITIAL) {
            $output->writeln(' [OK]');
        } else if ($export !== null) {
            $output->writeln(' [FAILED, INCORRECT STATUS]');
            return Command::FAILURE;
        } else {
            $output->writeln(' [FAILED]');
            return Command::FAILURE;
        }

        $output->write('Uploading metrics...');
        $this->exportService->upload($export);
        $output->writeln(' [OK]');

        return Command::SUCCESS;
    }
}
