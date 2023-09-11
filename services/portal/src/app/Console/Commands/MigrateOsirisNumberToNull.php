<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

use function boolval;
use function count;
use function intval;
use function max;
use function usleep;

/**
 * @codeCoverageIgnore
 */
class MigrateOsirisNumberToNull extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cases:migrate-osiris-empty
    {--chunk=1000 : The amount of cases to update per query}
    {--revert : Revert the migration; will update all rows from null to 0}
    {--usleep=100000 : Set the usleep after each chunk}
    ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to migrate OSIRIS number column values from 0 to null';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $chunkSize = (int) $this->option('chunk');
        $shouldRevert = boolval($this->option('revert'));
        $usleep = max(0, intval($this->option('usleep')));
        $currentValue = $shouldRevert ? null : 0;
        $targetValue = $shouldRevert ? 0 : null;

        $done = DB::table('covidcase')->where('osiris_number', $targetValue)->count();
        $todo = DB::table('covidcase')->where('osiris_number', $currentValue)->count();
        $total = $todo + $done;

        $progress = $this->output->createProgressBar($total);

        while ($rows = DB::table('covidcase')->where('osiris_number', $currentValue)->limit($chunkSize)->pluck('uuid')->toArray()) {
            DB::table('covidcase')->whereIn('uuid', $rows)->update(['osiris_number' => $targetValue]);
            $progress->advance(count($rows));

            if ($usleep !== 0) {
                usleep($usleep);
            }
        }

        $progress->finish();

        return Command::SUCCESS;
    }
}
