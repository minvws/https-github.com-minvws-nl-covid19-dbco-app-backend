<?php

namespace MinVWS\DBCO\Metrics\Commands;

use MinVWS\DBCO\Metrics\Services\IntakeExportService;
use MinVWS\Metrics\Commands\AbstractListExportsCommand;
use MinVWS\Metrics\Services\ExportService;
use Symfony\Component\Console\Input\InputOption;

/**
 * List exports.
 *
 * @package MinVWS\Metrics\Commands
 */
class ListIntakeExportsCommand extends AbstractListExportsCommand
{
    protected static $defaultName = 'intakes:list-exports';

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
            ->setDescription('List intake exports')
            ->setHelp('Can be used to list intake exports')
            ->addOption('limit', 'l', InputOption::VALUE_REQUIRED, 'Limit to the given amount of exports', 20)
            ->addOption('offset', 'o', InputOption::VALUE_REQUIRED, 'Start at the given export offset (ordered in descending order)', 0)
            ->addOption('status', 's', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Only list exports with the given statuse', ['initial', 'exported', 'uploaded']);
    }
}
