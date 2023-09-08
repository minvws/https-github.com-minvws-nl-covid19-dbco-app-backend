<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Output\ConsoleOutput;

class AddHpzoneNumber extends Migration
{
    public function up(): void
    {
        /** @var ConsoleOutput $output */
        $output = app(ConsoleOutput::class);

        // Migrate any cases created after the last pre-migration script run
        $exitCode = Artisan::call('migration:copy-hpzone-numbers');

        if ($exitCode !== 0) {
            throw new RuntimeException('migration:copy-hpzone-numbers failed, aborting migration');
        }

        $this->handleUpdatedCases($output);
    }

    public function down(): void
    {
        // I don't think we should revert this automatically.
    }

    /**
     * Some cases may have their case_id updated after the pre-migration has been run.
     * This function updates such cases.
     */
    private function handleUpdatedCases(ConsoleOutput $output): void
    {
        $caseCount = DB::select('SELECT COUNT(*) as cases_to_update FROM covidcase WHERE hpzone_number != case_id')[0]->cases_to_update;

        $output->writeln('Found ' . $caseCount . ' cases to update. ');
        $output->writeln('Running update query ...');

        DB::update("UPDATE covidcase
                SET hpzone_number = case_id
                WHERE hpzone_number != case_id");

        $output->writeln('Finished update query');
    }
}
