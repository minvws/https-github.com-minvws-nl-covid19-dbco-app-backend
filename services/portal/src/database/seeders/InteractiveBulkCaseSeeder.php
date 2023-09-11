<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use function compact;

class InteractiveBulkCaseSeeder extends Seeder
{
    public function run(): void
    {
        $casesToCreate = $this->command->ask('Please enter the the number of cases to create (default: 10)') ?? 10;
        $maxContextsToCreate = $this->command->ask('Please enter the the maximum number of contexts to create per case (default: 5)') ?? 5;
        $maxTestResultsToCreate = $this->command->ask(
            'Please enter the the maximum number of test results to create per case (default: 1)',
        ) ?? 1;

        $this->call(BulkCaseSeeder::class, false, compact('casesToCreate', 'maxContextsToCreate', 'maxTestResultsToCreate'));
    }
}
