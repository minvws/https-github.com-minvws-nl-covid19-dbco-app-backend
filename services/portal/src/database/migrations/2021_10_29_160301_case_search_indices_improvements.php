<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CaseSearchIndicesImprovements extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_aorg,
                ADD INDEX i_covidcase_planner_org (organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC),
                ADD INDEX i_covidcase_planner_aorg (assigned_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC)
        ");

        if (count(DB::select("SHOW INDEX FROM covidcase WHERE key_name = 'i_covidcase_planner'")) <= 0) {
            return;
        }

        // remove old index that doesn't exist everywhere
        DB::statement("ALTER TABLE covidcase DROP INDEX i_covidcase_planner");
    }

    public function down(): void
    {
        // not useful to roll the index back
    }
}
