<?php
namespace App\Application\Commands;

use App\Application\Services\TaskService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshGeneralTasksCommand extends Command
{
    protected static $defaultName = 'task:refresh-general';

    /**
     * @var TaskService
     */
    private TaskService $taskService;

    /**
     * Constructor.
     *
     * @param TaskService $taskService
     */
    public function __construct(TaskService $taskService)
    {
        parent::__construct();
        $this->taskService = $taskService;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Refresh general tasks command')
            ->setHelp('Can be used to retrieve a fresh list of general tasks from the health authority');
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
        $this->taskService->refreshGeneralTasks();
        return Command::SUCCESS;
    }
}
