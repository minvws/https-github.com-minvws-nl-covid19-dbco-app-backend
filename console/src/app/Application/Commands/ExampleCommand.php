<?php
namespace App\Application\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommand extends Command
{
    protected static $defaultName = 'example';

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Example command')
            ->setHelp('An example command');
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
        $output->writeln('Hello World!');
        return Command::SUCCESS;
    }
}
