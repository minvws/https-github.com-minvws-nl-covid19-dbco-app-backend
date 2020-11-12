<?php
namespace DBCO\Worker\Application\Commands;

use DBCO\Worker\Application\Services\PairingService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessPairingQueueCommand extends Command
{
    protected static $defaultName = 'pairing:process-queue';

    /**
     * @var PairingService
     */
    private PairingService $pairingService;

    /**
     * Constructor.
     *
     * @param PairingService $pairingService
     */
    public function __construct(PairingService $pairingService)
    {
        parent::__construct();
        $this->pairingService = $pairingService;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Process pairing queue command')
            ->setHelp('Can be used to process entries in the pairing queue (e.g. exchange keys)');
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
                $this->pairingService->processPairingQueueEntry();
            } catch (\Throwable $e) {
                // wait a little before trying again, maybe something is down
                sleep(1);
            }
        }

        return Command::SUCCESS;
    }
}
