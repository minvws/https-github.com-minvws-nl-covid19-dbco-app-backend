<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Console\Commands\Migration\PersonDateOfBirthEncrypt;
use Illuminate\Console\Command;

class MigrateDataOffHours extends Command
{
    /** @var string $signature */
    protected $signature = 'migrate:data:off-hours';

    /** @var string $description */
    protected $description = 'Run data migrations (usally in off-hours)';

    public function handle(): int
    {
        $this->info('Running off-hour data migrations...');

        // call your command here, since the command is run every minute it should only migrate a small set of data
        $this->call(PersonDateOfBirthEncrypt::class);

        return Command::SUCCESS;
    }
}
