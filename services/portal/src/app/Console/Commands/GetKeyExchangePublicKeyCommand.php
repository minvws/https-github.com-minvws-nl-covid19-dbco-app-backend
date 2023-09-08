<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SecurityService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function base64_encode;

#[AsCommand(name: 'security:get-key-exchange-public-key', description: 'Print the key exchange public key')]
class GetKeyExchangePublicKeyCommand extends Command
{
    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();

        $this->securityService = $securityService;
    }

    protected function configure(): void
    {
        $this->setHelp(
            'Can be used to print the key exchange public key that is used for the initial encryption of the client public key during the key exchange process.',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $publicKey = $this->securityService->getKeyExchangePublicKey();
        if ($publicKey !== null) {
            $output->writeln(base64_encode($publicKey));
            return self::SUCCESS;
        }

        $output->writeln("ERROR: Key does not exist!");
        return self::FAILURE;
    }
}
