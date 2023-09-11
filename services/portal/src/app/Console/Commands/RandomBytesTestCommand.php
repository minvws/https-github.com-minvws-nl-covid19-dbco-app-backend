<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

use function asort;
use function count;
use function max;
use function min;
use function random_bytes;
use function sprintf;
use function strlen;

use const SORT_NUMERIC;

/**
 * Test randomness of PHP's random_bytes function.
 */
#[AsCommand(name: 'test:random-bytes', description: 'Test randomness of PHP\'s random_bytes function')]
class RandomBytesTestCommand extends Command
{
    private const DEFAULT_TOTAL_BYTES = 1024 * 1024 * 1024;
    private const DEFAULT_STEP_SIZE = 1024;

    protected function configure(): void
    {
        $this->setHelp('Can be used to test the randomness of PHP\'s random_bytes method in a certain environment')
            ->addOption('total-bytes', 't', InputOption::VALUE_REQUIRED, 'Total number of bytes to generate', self::DEFAULT_TOTAL_BYTES)
            ->addOption('step-size', 's', InputOption::VALUE_REQUIRED, 'Number of bytes to generate at once', self::DEFAULT_STEP_SIZE);
    }

    /**
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $totalBytes = $input->getOption('total-bytes');
        $stepSize = $input->getOption('step-size');

        $table = [];
        for ($offset = 0; $offset < $totalBytes; $offset += $stepSize) {
            $length = min($totalBytes - $offset, $stepSize);
            Assert::integer($length);
            if ($length <= 0) {
                throw new InvalidArgumentException("Length must be greater than 0");
            }
            $bytes = random_bytes($length);
            $bytesSize = strlen($bytes);
            for ($i = 0; $i < $bytesSize; $i++) {
                $byte = $bytes[$i];
                $table[$byte] ??= 0;
                ++$table[$byte];
            }
        }

        asort($table, SORT_NUMERIC);

        $output->writeln(sprintf('Total unique bytes           : %d', count($table)));
        $output->writeln(sprintf('Highest number of occurrences: %d', max($table)));
        $output->writeln(sprintf('Lowest number of occurrences : %d', min($table)));

        return Command::SUCCESS;
    }
}
