<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SecurityService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Webmozart\Assert\Assert;

#[AsCommand(
    name: 'security:create-key-exchange-secret-key',
    description: 'Create secret key for storing/retrieving data from the datastore',
)]
class CreateKeyExchangeSecretKeyCommand extends Command
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();

        $this->securityService = $securityService;
    }

    protected function configure(): void
    {
        $this->setHelp('Can be used to create the secret key for storing/retrieving data from the datastore in the security module')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force overwrite of existing key');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $force = $input->getOption('force');
        Assert::boolean($force);
        $result = $this->securityService->createKeyExchangeSecretKey($force);
        $output->writeln($result ? "Key successfully created!" : "ERROR: Key already exists!");
        return self::SUCCESS;
    }
}
