<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPlannerCaseSortIndexes extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_aorg,
                DROP INDEX i_covidcase_planner_corg,
                DROP INDEX i_covidcase_planner_cl,

                ADD INDEX i_covidcase_planner_org (organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_aorg (assigned_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, bco_status, assigned_user_uuid, current_organisation_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed)
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_aorg,
                DROP INDEX i_covidcase_planner_corg,
                DROP INDEX i_covidcase_planner_cl,

                ADD INDEX i_covidcase_planner_org (organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test),
                ADD INDEX i_covidcase_planner_aorg (assigned_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test),
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, bco_status, assigned_user_uuid, current_organisation_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test)
        ");
    }
}
