<?php
namespace MinVWS\Metrics\Commands;

use MinVWS\Metrics\Models\Export;
use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * List exports.
 *
 * @package MinVWS\Metrics\Commands
 */
class ListExportsCommand extends Command
{
    protected static $defaultName = 'metrics:list-exports';

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
            ->setDescription('List metric exports')
            ->setHelp('Can be used to list metric exports')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit to the given amount of exports', 20)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'Start at the given export offset (ordered in descending order)', 0)
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Only list exports with the given statuse', ['initial', 'exported', 'uploaded']);
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
        $limit = max(0, (int)$input->getOption('limit'));
        $offset = max(0, (int)$input->getOption('offset'));
        $status = $input->getOption('status');

        $count = $this->exportService->countExports($status);
        if ($count === 0) {
            $output->writeln('0 exports found with the given status');
            return Command::SUCCESS;
        }

        if ($offset > $count) {
            $output->writeln(sprintf('Invalid offset, only %d exports found', $count));
            return Command::FAILURE;
        }

        $exports = $this->exportService->listExports($limit, $offset, $status);

        $table = new Table($output);
        $table->setHeaders(['UUID', 'Created', 'Status', 'Exported', 'Filename', 'Uploaded', '#Events']);
        $table->setRows(
            array_map(
                fn (Export $e) => [
                    $e->uuid,
                    $e->createdAt->format('Y-m-d H:i:s'),
                    $e->status,
                    $e->exportedAt !== null ? $e->exportedAt->format('Y-m-d H:i:s') : '',
                    $e->filename ?? '',
                    $e->uploadedAt !== null ? $e->uploadedAt->format('Y-m-d H:i:s') : '',
                    $e->eventCount
                ],
                $exports
            )
        );
        $table->setFooterTitle(sprintf('%d-%d of %d', $offset + 1, min($count, $offset + $limit), $count));
        $table->render();

        return Command::SUCCESS;
    }
}
