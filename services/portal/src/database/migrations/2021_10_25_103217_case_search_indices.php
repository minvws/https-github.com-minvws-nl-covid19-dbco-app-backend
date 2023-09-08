<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CaseSearchIndices extends Migration
{
    public function up(): void
    {
        if (count(DB::select("SHOW INDEX FROM covidcase WHERE key_name = 'i_covidcase_planner_org'")) > 0) {
            return; // already run manually
        }

        if (count(DB::select("SHOW INDEX FROM covidcase WHERE key_name = 'i_covidcase_case_id'")) > 0) {
            // remove old index
            DB::statement("ALTER TABLE covidcase DROP INDEX i_covidcase_case_id");
        }

        DB::statement("
            ALTER TABLE covidcase
                ADD INDEX i_covidcase_case_id (case_id),
                ADD INDEX i_covidcase_search_date_of_birth (search_date_of_birth, organisation_uuid, assigned_organisation_uuid, deleted_at),
                ADD INDEX i_covidcase_search_email (search_email, organisation_uuid, assigned_organisation_uuid, deleted_at),
                ADD INDEX i_covidcase_search_phone (search_phone, organisation_uuid, assigned_organisation_uuid, deleted_at),
                ADD INDEX i_covidcase_planner_org (organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, assigned_organisation_uuid, deleted_at, updated_at),
                ADD INDEX i_covidcase_planner_aorg (assigned_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at)
        ");

        DB::statement("ALTER TABLE case_assignment_history ADD INDEX i_case_assignment_history_order (covidcase_uuid, assigned_at DESC)");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_case_id,
                DROP INDEX i_covidcase_search_date_of_birth,
                DROP INDEX i_covidcase_search_email,
                DROP INDEX i_covidcase_search_phone,
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_aorg
        ");

        DB::statement("ALTER TABLE case_assignment_history DROP INDEX i_case_assignment_history_order");
    }
}
