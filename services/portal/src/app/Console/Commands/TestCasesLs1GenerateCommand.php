<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Database\Seeders\TestCasesLs1Seeder;
use Illuminate\Console\Command;

class TestCasesLs1GenerateCommand extends Command
{
    protected $signature = 'test-cases-ls1:generate {totalCasesToCreate=100} {--chunkSize=10}';
    protected $description = 'Creates test-cases for the LS1 (outssourced) organisation';

    public function handle(TestCasesLs1Seeder $testCasesLs1Seeder): int
    {
        $totalCasesToCreate = (int) $this->argument('totalCasesToCreate');
        $chunkSize = $this->option('chunkSize') ? (int) $this->option('chunkSize') : 10;

        $testCasesLs1Seeder->run($totalCasesToCreate, $chunkSize);

        $this->output->writeln('');

        return self::SUCCESS;
    }
}
