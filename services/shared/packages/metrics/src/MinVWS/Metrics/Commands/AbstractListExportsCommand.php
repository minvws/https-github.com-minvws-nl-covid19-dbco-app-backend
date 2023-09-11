<?php

namespace MinVWS\Metrics\Commands;

use MinVWS\Metrics\Models\Export;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\Table;

/**
 * List exports.
 *
 * @package MinVWS\Metrics\Commands
 */
class AbstractListExportsCommand extends Command
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
        $table->setHeaders(['UUID', 'Created', 'Status', 'Exported', 'Filename', 'Uploaded', 'Count']);
        $table->setRows(
            array_map(
                fn (Export $e) => [
                    $e->uuid,
                    $e->createdAt->format('Y-m-d H:i:s'),
                    $e->status,
                    $e->exportedAt !== null ? $e->exportedAt->format('Y-m-d H:i:s') : '',
                    $e->filename ?? '',
                    $e->uploadedAt !== null ? $e->uploadedAt->format('Y-m-d H:i:s') : '',
                    $e->itemCount
                ],
                $exports
            )
        );
        $table->setFooterTitle(sprintf('%d-%d of %d', $offset + 1, min($count, $offset + $limit), $count));
        $table->render();

        return Command::SUCCESS;
    }
}
