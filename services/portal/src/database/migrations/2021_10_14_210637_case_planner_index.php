<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CasePlannerIndex extends Migration
{
    public function up(): void
    {
        if (count(DB::select("SHOW INDEX FROM covidcase WHERE key_name = 'i_covidcase_planner'")) > 0) {
            return;
        }

        DB::statement(
            "CREATE INDEX i_covidcase_planner ON covidcase (assigned_organisation_uuid, assigned_case_list_uuid, assigned_user_uuid, bco_status, deleted_at, updated_at)",
        );
    }

    public function down(): void
    {
        DB::statement("DROP INDEX i_covidcase_planner ON covidcase");
    }
}
