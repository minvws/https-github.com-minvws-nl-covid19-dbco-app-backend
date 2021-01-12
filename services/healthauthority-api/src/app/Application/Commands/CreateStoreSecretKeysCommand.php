<?php
namespace DBCO\HealthAuthorityAPI\Application\Commands;

use DateTimeImmutable;
use DBCO\HealthAuthorityAPI\Application\Services\SecurityService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Create store key command.
 *
 * @package DBCO\HealthAuthorityAPI\Application\Commands
 */
class CreateStoreSecretKeysCommand extends Command
{
    protected static $defaultName = 'security:create-store-secret-keys';

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
            ->setDescription('Create secret keys for storing/retrieving data from the datastore')
            ->setHelp('Can be used to create the secret keys for storing/retrieving data from the datastore in the security module');
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
        try {
            $this->securityService->manageStoreSecretKeys(
                function (DateTimeImmutable $day, string $mutation, ?Exception $exception = null) use ($output) {
                    $output->writeln(
                        sprintf(
                            'Create store key for day %s... [%s]',
                            $day->format('Y-m-d'),
                            strtoupper($mutation)
                        )
                    );

                    if ($exception !== null) {
                        $output->writeln('ERROR: ' . $exception->getMessage());
                    }
                },
            );

            return Command::SUCCESS;
        } catch (Exception $e) {
            $output->writeln('ERROR: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return $result ? Command::SUCCESS : Command::FAILURE;
    }
}
