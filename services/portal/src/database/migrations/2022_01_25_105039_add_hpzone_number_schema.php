<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Console\Output\ConsoleOutput;

class AddHpzoneNumberSchema extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        /** @var ConsoleOutput $output */
        $output = app(ConsoleOutput::class);

        if (!Schema::hasTable('bco_numbers')) {
            $output->writeln('Creating bco_numbers');

            Schema::create('bco_numbers', static function (Blueprint $table): void {
                $table->uuid('uuid')->primary();
                $table->char('bco_number', 20)->unique();
            });
        } else {
            $output->writeln('Table bco_numbers already exists, skipping migration');
        }

        if (!Schema::hasColumn('covidcase', 'hpzone_number')) {
            $output->writeln('Updating covidcase schema...');

            DB::statement('alter table `covidcase` add `hpzone_number` char(10) null after `case_number`,
            add index `covidcase_hpzone_number_unique`(`hpzone_number`, `case_id`)');
        } else {
            $output->writeln('Table covidcase already has column hpzone_number, skipping');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('bco_numbers');

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('hpzone_number');
        });
    }
}
