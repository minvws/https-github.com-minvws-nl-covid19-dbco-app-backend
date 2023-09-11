<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class UpdatePlannerViewCols extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $query = DB::table('covidcase')->select(['uuid']);

        $output = new ConsoleOutput();
        $output->writeln("Retrieving count for progress update...");
        $count = $query->count('uuid');
        $output->writeln("Going to migrate $count records...");
        $bar = new ProgressBar($output, $count);
        $bar->start();

        $query->orderBy('uuid')->chunkById(100, static function (Collection $cases) use ($bar): void {
            $uuids = $cases->pluck('uuid');
            DB::table('covidcase')
                ->whereIn('uuid', $uuids)
                ->update([
                    // will be set to the correct value by the update trigger
                    'organisation_planner_view' => 'unsupported',
                    'current_organisation_planner_view' => 'unsupported',
                    'case_list_planner_view' => 'unsupported',
                ]);

            $bar->advance($cases->count());
        }, 'uuid');

        $bar->finish();

        $output->writeln("");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
