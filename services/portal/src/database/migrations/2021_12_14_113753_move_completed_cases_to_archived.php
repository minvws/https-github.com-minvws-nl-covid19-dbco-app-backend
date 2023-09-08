<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\ConsoleOutput;

class MoveCompletedCasesToArchived extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $countQuery = EloquentCase::where('bco_status', BCOStatus::completed()->value)
            ->whereNull('assigned_user_uuid')
            ->whereNull('assigned_case_list_uuid');

        $output = app(ConsoleOutput::class);

        $bar = new ProgressBar($output, (int) ceil($countQuery->count() / 20_000));
        $bar->start();

        do {
            $affectedRows = DB::update("UPDATE covidcase
        SET bco_status = 'archived', is_approved = null
        WHERE bco_status = 'completed'
          AND assigned_user_uuid IS NULL
          AND assigned_case_list_uuid IS NULL LIMIT 20000");
            $bar->advance();
        } while ($affectedRows > 0);

        $bar->finish();
        $output->writeln('done');
    }

    public function down(): void
    {
    }
}
