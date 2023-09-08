<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement("CREATE INDEX `i_place_mutation` ON `place` (`updated_at`, `uuid`, `organisation_uuid`)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `place` DROP INDEX `i_place_mutation`");
    }
};
