<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddCaseListStatsIndex extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase ADD INDEX i_covidcase_case_list_stats (
                assigned_case_list_uuid,
                (IF(deleted_at IS NOT NULL, 1, 0)),
                bco_status,
                (IF(assigned_user_uuid IS NOT NULL, 1, 0)),
                is_approved
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE covidcase DROP INDEX i_covidcase_case_list_stats");
    }
}
