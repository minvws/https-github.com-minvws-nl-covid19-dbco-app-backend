<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SecurityService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'security:cache-keys', description: 'Load keys from the security module into memory')]
class CacheKeysCommand extends Command
{
    protected static $defaultName = 'security:cache-keys';

    private SecurityService $securityService;

    public function __construct(SecurityService $securityService)
    {
        parent::__construct();

        $this->securityService = $securityService;
    }

    protected function configure(): void
    {
        $this->setHelp('Can be used to load keys from the security module into Redis memory');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->securityService->cacheKeys();
        $output->writeln("Keys cached successfully!");
        return self::SUCCESS;
    }
}
