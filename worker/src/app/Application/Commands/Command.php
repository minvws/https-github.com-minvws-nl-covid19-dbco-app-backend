<?php
namespace App\Application\Commands;

use Exception;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Command base class.
 *
 * @package App\Application\Commands
 */
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    /**
     * Helper method for logging an exception to the console.
     *
     * @param Exception $exception
     *
     * @param OutputInterface $output
     */
    protected function outputException(Exception $exception, OutputInterface $output)
    {
        $output->writeln('<error>' . $exception->getMessage() . '</error>');
        $output->writeln('<error>' . $exception->getTraceAsString() . '</error>');
        if ($exception->getPrevious()) {
            $output->writeln('<error>' . $exception->getPrevious()->getMessage() . '</error>');
            $output->writeln('<error>' . $exception->getPrevious()->getTraceAsString() . '</error>');
        }
    }
}
