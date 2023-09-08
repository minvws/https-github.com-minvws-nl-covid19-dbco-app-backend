<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("CREATE INDEX `i_covidcase_mutation` ON `covidcase` (`updated_at`, `uuid`, `organisation_uuid`, `deleted_at`)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `covidcase` DROP INDEX `i_covidcase_mutation`");
    }
};
