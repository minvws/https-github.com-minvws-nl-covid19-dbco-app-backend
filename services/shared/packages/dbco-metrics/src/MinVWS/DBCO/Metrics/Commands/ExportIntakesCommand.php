<?php

namespace MinVWS\DBCO\Metrics\Commands;

use Exception;
use Illuminate\Support\Str;
use MinVWS\DBCO\Metrics\Enums\IntakeType;
use MinVWS\DBCO\Metrics\Services\IntakeExportService;
use MinVWS\Metrics\Commands\AbstractExportMetricsCommand;
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
class ExportIntakesCommand extends AbstractExportMetricsCommand
{
    protected static $defaultName = 'intakes:export';

    protected IntakeExportService $exportService;

    /**
     * @var ?string Type of intake items (bco, selftest)
     */
    private ?string $type = null;

    /**
     * Constructor.
     *
     * @param IntakeExportService $exportService
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
            ->setDescription('Export intakes')
            ->setHelp('Can be used to export intakes by type (bco, selftest)')
            ->addArgument('exportUuid', InputArgument::OPTIONAL, 'Export UUID, can be used to re-export an existing set of exported intakes', null)
            ->addOption('upload', 'u', InputOption::VALUE_NONE, 'Upload after export')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit the number of intakes to export', null)
            ->addOption('type', 't', InputOption::VALUE_REQUIRED, 'Select the type of intake (bco, selftest)', null);
    }

    /**
     * Export.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return Export
     *
     * @throws Exception
     */
    protected function export(InputInterface $input, OutputInterface $output): Export
    {
        $exportUuid = $input->getArgument('exportUuid', null);
        $this->type = $input->getOption('type', null);
        $typeOptions = [IntakeType::BCO, IntakeType::SELFTEST];
        if (!is_null($this->type) && !in_array($this->type, $typeOptions)) {
            throw new Exception('Unknown type option. Possible values: ' . implode($typeOptions));
        }

        $limit = null;
        if ($exportUuid === null) {
            $limit = $input->getOption('limit');
        }

        if (is_numeric($limit) && $limit > 0) {
            $limit = (int)$limit;
            $output->writeln('Applying limit: ' . $limit);
        } else {
            $limit = null;
        }

        $output->write($this->getOutputMessage());

        try {
            $export = $this->exportService->export($exportUuid, $limit, $this->type);
        } catch (Exception $e) {
            $output->writeln(' [ERROR]');
            throw $e;
        }

        $output->writeln(' [OK]');
        $output->writeln('');
        $output->writeln(sprintf('Export UUID     : %s', $export->uuid));
        $output->writeln(sprintf('Export filename : %s', $export->filename));
        $output->writeln(sprintf(Str::ucfirst($this->type) . ' count      : %d', $export->itemCount));

        return $export;
    }

    protected function getOutputMessage(): string
    {
        return 'Exporting intakes' . ($this->type ? ' of type ' . $this->type : '') . '...';
    }
}
