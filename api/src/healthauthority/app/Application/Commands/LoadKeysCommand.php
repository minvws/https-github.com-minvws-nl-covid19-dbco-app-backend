<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Load security module keys.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Commands
 */
class LoadKeysCommand extends Command
{
    protected static $defaultName = 'security:load-keys';

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
            ->setDescription('Load keys from the security module into memory')
            ->setHelp('Can be used to load keys from the security module into Redis memory');
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
        $this->securityService->loadKeys();
        return Command::SUCCESS;
    }
}
