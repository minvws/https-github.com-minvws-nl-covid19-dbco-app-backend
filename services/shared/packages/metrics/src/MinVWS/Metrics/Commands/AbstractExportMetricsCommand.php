<?php

namespace MinVWS\Metrics\Commands;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use MinVWS\Metrics\Models\Export;

/**
 * Export metrics.
 *
 * @package MinVWS\Metrics\Commands
 */
abstract class AbstractExportMetricsCommand extends Command
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
        try {
            $export = $this->export($input, $output);

            if ($input->getOption('upload')) {
                $output->writeln('');
                $this->upload($export, $output);
            }
        } catch (Exception $e) {
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
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
            $export = $this->exportService->export($exportUuid, $limit);
        } catch (Exception $e) {
            $output->writeln(' [ERROR]');
            throw $e;
        }

        $output->writeln(' [OK]');
        $output->writeln('');
        $output->writeln(sprintf('Export UUID     : %s', $export->uuid));
        $output->writeln(sprintf('Export filename : %s', $export->filename));
        $output->writeln(sprintf('Export count      : %d', $export->itemCount));

        return $export;
    }

    /**
     * Upload export.
     *
     * @param Export          $export
     * @param OutputInterface $output
     *
     * @throws Exception
     */
    protected function upload(Export $export, OutputInterface $output)
    {
        $output->write($this->getOutputMessage());

        try {
            $this->exportService->upload($export);
        } catch (Exception $e) {
            $output->writeln(' [ERROR]');
            throw $e;
        }

        $output->writeln(' [OK]');
    }

    abstract protected function getOutputMessage(): string;
}
