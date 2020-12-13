<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create store key command.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Commands
 */
class CreateStoreSecretKeyCommand extends Command
{
    protected static $defaultName = 'security:create-store-secret-key';

    /**
     * @var SecurityService
     */
    private SecurityService $securityService;

    /**
     * Constructor.
     *
     * @param SecurityService $securityService
     */
    public function __construct(SecurityService $securityService)
    {
        parent::__construct();
        $this->securityService = $securityService;
    }

    /**
     * Configure command.
     */
    protected function configure()
    {
        $this
            ->setDescription('Create secret key for storing/retrieving data from the datastore')
            ->setHelp('Can be used to create the secret key for storing/retrieving data from the datastore in the security module')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force overwrite of existing key');
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
        $result = $this->securityService->createStoreSecretKey($input->getOption('force'));
        $output->writeln($result ? "Key successfully created!" : "ERROR: Key already exists!");
        return $result ? Command::SUCCESS : Command::FAILURE;
    }
}
