<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateCaseSearchIndexForIsApproved extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_corg,
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed, is_approved);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_corg,
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed);
        ");
    }
}
