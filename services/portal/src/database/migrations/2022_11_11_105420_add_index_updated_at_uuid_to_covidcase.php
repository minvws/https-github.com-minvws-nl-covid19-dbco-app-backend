<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE `covidcase` ADD INDEX `i_updated_at_uuid` (`updated_at` DESC, `uuid` ASC)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `covidcase` DROP INDEX `i_updated_at_uuid`');
    }
};
