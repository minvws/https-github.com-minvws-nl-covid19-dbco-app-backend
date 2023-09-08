<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CaseIndexImprovements extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_aorg,
                DROP INDEX i_covidcase_planner_corg,
                ADD INDEX i_covidcase_planner_org (organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test),
                ADD INDEX i_covidcase_planner_aorg (assigned_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test),
                ADD INDEX i_covidcase_index_status_timeout (bco_status, index_status, pairing_expires_at, deleted_at),
                ADD INDEX i_covidcase_index_status_expired (bco_status, index_status, index_submitted_at, window_expires_at, deleted_at)
        ");
    }

    public function down(): void
    {
        // only drop the new indices, changing the old ones back has no real use
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_index_status_timeout,
                DROP INDEX i_covidcase_index_status_expired;
        ");
    }
}
