<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Test randomness of PHP's random_bytes function.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Commands
 */
class RandomBytesTestCommand extends Command
{
    const DEFAULT_TOTAL_BYTES = 1024 * 1024 * 1024;
    const DEFAULT_STEP_SIZE = 1024;

    protected static $defaultName = 'test:random-bytes';

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Tests the randomness of PHP\'s random_bytes method')
            ->setHelp('Can be used to test the randomness of PHP\'s random_bytes method in a certain environment')
            ->addOption('total-bytes', 't', InputOption::VALUE_REQUIRED, 'Total number of bytes to generate', self::DEFAULT_TOTAL_BYTES)
            ->addOption('step-size', 's', InputOption::VALUE_REQUIRED, 'Number of bytes to generate at once', self::DEFAULT_STEP_SIZE);
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
        $totalBytes = $input->getOption('total-bytes');
        $stepSize = $input->getOption('step-size');

        $table = [];
        for ($offset = 0; $offset < $totalBytes; $offset = $offset + $stepSize) {
            $length = min($totalBytes - $offset, $stepSize);
            $bytes = random_bytes($length);
            for ($i = 0; $i < strlen($bytes); $i++) {
                $byte = $bytes[$i];
                $table[$byte] ??= 0;
                $table[$byte] += 1;
            }
        }

        asort($table, SORT_NUMERIC);

        $output->writeln(sprintf('Total unique bytes           : %d', count($table)));
        $output->writeln(sprintf('Highest number of occurrences: %d', max($table)));
        $output->writeln(sprintf('Lowest number of occurrences : %d', min($table)));

        return Command::SUCCESS;
    }
}
