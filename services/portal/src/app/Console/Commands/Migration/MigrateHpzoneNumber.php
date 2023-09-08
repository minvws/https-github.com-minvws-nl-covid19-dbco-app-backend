<?php

declare(strict_types=1);

namespace App\Console\Commands\Migration;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

use function app;
use function ceil;
use function collect;
use function count;

use const PHP_EOL;

class MigrateHpzoneNumber extends Command
{
    /** @var string */
    protected $signature = 'migration:copy-hpzone-numbers {--batchSize=}';

    /** @var string */
    protected $description = 'BCO Portal number pre-migration. Copy the HPZone numbers in covidcase.case_id field to the covidcase.hpzone_number field.';

    public function handle(): int
    {
        $invalidCases = DB::select('SELECT case_id from covidcase where LENGTH(case_id) > 8');

        if (count($invalidCases) > 0) {
            $this->getOutput()->error(
                "The following case_id's are invalid and need to be manually corrected to be numbers of max 8 chars in length.
If the invalid case_id's are BCO Portaal numbers, you are running this command after 1.18 release. This is wrong!",
            );
            $this->getOutput()->error(collect($invalidCases)->pluck('case_id')->join(', '));
            return 1;
        }

        $batchSize = (int) $this->option('batchSize');
        if ($batchSize < 1) {
            $batchSize = 20_000;
        }

        $output = app(ConsoleOutput::class);
        $output->writeln('Migrating hpzone numbers with batch size: ' . $batchSize);

        $this->migrate($output, $batchSize);

        return 0;
    }

    private function migrate(ConsoleOutput $output, int $batchSize): void
    {
        $caseCount = DB::select(
            'SELECT COUNT(*) as cases_to_migrate FROM covidcase WHERE hpzone_number IS NULL AND case_id IS NOT NULL',
        )[0]->cases_to_migrate;

        $batchCount = (int) ceil($caseCount / $batchSize);

        $bar = new ProgressBar($output, $batchCount);
        $bar->start();

        do {
            $affectedRows = DB::update("UPDATE covidcase
                SET hpzone_number = case_id
                WHERE hpzone_number IS NULL AND case_id IS NOT NULL
                LIMIT " . $batchSize);
            $bar->advance();
        } while ($affectedRows > 0);

        $bar->finish();
        $output->writeln(PHP_EOL . 'Finished migrating HPZone numbers');
    }
}
