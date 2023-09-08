<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropCaseNumber extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_corg,
                DROP INDEX i_covidcase_planner_cl,
                DROP INDEX covidcase_case_number_index,
                DROP COLUMN case_number,
                ADD INDEX i_covidcase_planner_org (organisation_uuid, organisation_planner_view, assigned_case_list_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, current_organisation_planner_view, organisation_uuid, assigned_case_list_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, case_list_planner_view, current_organisation_uuid, organisation_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, status_index_contact_tracing, status_contacts_informed)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_corg,
                DROP INDEX i_covidcase_planner_cl,
                ADD COLUMN `case_number` varchar(255) null,
                ADD INDEX `covidcase_case_number_index`(`case_number`),
                ADD INDEX i_covidcase_planner_org (organisation_uuid, organisation_planner_view, assigned_case_list_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, current_organisation_planner_view, organisation_uuid, assigned_case_list_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, case_list_planner_view, current_organisation_uuid, organisation_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed)
        ");
    }
}
