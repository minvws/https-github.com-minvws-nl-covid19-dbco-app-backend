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
        DB::statement("
            UPDATE place
            SET index_count_reset_at = '2022-10-01 00:00:00'
            WHERE index_count_reset_at IS NULL;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            UPDATE place
            SET index_count_reset_at = NULL
            WHERE index_count_reset_at = '2022-10-01 00:00:00';
        ");
    }
};
