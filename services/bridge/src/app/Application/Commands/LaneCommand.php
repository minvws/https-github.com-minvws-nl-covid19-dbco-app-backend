<?php
namespace DBCO\Bridge\Application\Commands;

use DBCO\Bridge\Application\Services\LaneService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class LaneCommand extends Command
{
    /**
     * @var LaneService
     */
    private LaneService $laneService;

    /**
     * Constructor.
     *
     * @param string      $name
     * @param string      $description
     * @param LaneService $laneService
     */
    public function __construct(string $name, string $description, LaneService $laneService)
    {
        parent::__construct();
        $this->setName('process:' . $name);
        $this->setDescription($description);
        $this->laneService = $laneService;
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
        while (true) {
            try {
                $this->laneService->processRequest(60);
            } catch (Throwable $e) {
                // wait a little before trying again, maybe something is down
                sleep(1);
            }
        }

        return Command::SUCCESS;
    }
}
