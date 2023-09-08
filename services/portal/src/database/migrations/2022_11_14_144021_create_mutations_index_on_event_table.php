<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        if (count(DB::select("SHOW INDEX FROM `event` WHERE key_name = 'i_event_mutation'")) > 0) {
            return;
        }

        DB::statement("CREATE INDEX `i_event_mutation` ON `event` (`created_at`, `uuid`, `organisation_uuid`)");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `event` DROP INDEX `i_event_mutation`");
    }
};
