<?php

namespace MinVWS\Metrics\Commands;

use Exception;
use MinVWS\Metrics\Models\Export;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Upload metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
abstract class AbstractUploadMetricsCommand extends Command
{
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
        } elseif ($export !== null) {
            $output->writeln(' [FAILED, INCORRECT STATUS]');
            return Command::FAILURE;
        } else {
            $output->writeln(' [FAILED]');
            return Command::FAILURE;
        }

        $output->write($this->getOutputMessage());
        try {
            $this->exportService->upload($export);
        } catch (Exception $e) {
            $output->writeln(' [ERROR]');
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        $output->writeln(' [OK]');

        return Command::SUCCESS;
    }

    abstract protected function getOutputMessage(): string;
}
