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
        DB::unprepared(
            <<<'SQL'
                UPDATE `chore`
                    SET `owner_resource_type` = 'bco-user'
                    WHERE `owner_resource_type` = 'user'
                SQL,
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared(
            <<<'SQL'
                UPDATE `chore`
                    SET `owner_resource_type` = 'user'
                    WHERE `owner_resource_type` = 'bco-user'
                SQL,
        );
    }
};
