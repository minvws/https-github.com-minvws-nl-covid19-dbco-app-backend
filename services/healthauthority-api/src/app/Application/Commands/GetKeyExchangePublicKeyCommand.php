<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use SodiumException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Get key exchange public key command.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Commands
 */
class GetKeyExchangePublicKeyCommand extends Command
{
    protected static $defaultName = 'security:get-key-exchange-public-key';

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
            ->setDescription('Print the key exchange public key')
            ->setHelp('Can be used to print the key exchange public key that is used for the initial encryption of the client public key during the key exchange process.');
    }

    /**
     * Execute command.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     *
     * @return int
     *
     * @throws SodiumException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $publicKey = $this->securityService->getKeyExchangePublicKey();
        if ($publicKey !== null) {
            $output->writeln(base64_encode($publicKey));
            return Command::SUCCESS;
        } else {
            $output->writeln("ERROR: Key does not exist!");
            return Command::FAILURE;
        }
    }
}
