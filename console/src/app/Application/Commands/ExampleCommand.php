<?php
namespace App\Application\Commands;

use App\Application\Services\ExampleService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ExampleCommand extends Command
{
    protected static $defaultName = 'example';

    /**
     * @var \App\Application\Services\ExampleService
     */
    private ExampleService $exampleService;

    /**
     * ExampleCommand constructor.
     *
     * @param \App\Application\Services\ExampleService $exportService
     */
    public function __construct(ExampleService $exportService)
    {
        parent::__construct();
        $this->exampleService = $exportService;
    }

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
        $example = $this->exampleService->example();
        $output->writeln(print_r($example, true));
        return Command::SUCCESS;
    }
}
