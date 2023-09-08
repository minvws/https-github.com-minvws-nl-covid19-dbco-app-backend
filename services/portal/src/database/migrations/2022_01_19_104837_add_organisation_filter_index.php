<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddOrganisationFilterIndex extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                modify bco_status varchar(15) default 'draft' not null,
                DROP INDEX i_covidcase_planner_cl,
                DROP INDEX i_covidcase_planner_corg,
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid asc, bco_status asc, assigned_user_uuid asc, current_organisation_uuid asc, assigned_organisation_uuid asc, deleted_at asc, organisation_uuid asc, updated_at desc, created_at asc, date_of_test asc, priority desc, case_number asc, status_index_contact_tracing asc, status_contacts_informed asc),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid asc, bco_status asc, assigned_case_list_uuid asc, assigned_user_uuid asc, deleted_at asc, organisation_uuid asc, updated_at desc, created_at asc, date_of_test asc, priority desc, case_number asc, status_index_contact_tracing asc, status_contacts_informed asc, is_approved asc)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                modify bco_status varchar(255) default 'draft' not null,
                DROP INDEX i_covidcase_planner_cl,
                DROP INDEX i_covidcase_planner_corg,
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid asc, bco_status asc, assigned_user_uuid asc, current_organisation_uuid asc, assigned_organisation_uuid asc, deleted_at asc, updated_at desc, created_at asc, date_of_test asc, priority desc, case_number asc, status_index_contact_tracing asc, status_contacts_informed asc),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid asc, bco_status asc, assigned_case_list_uuid asc, assigned_user_uuid asc, deleted_at asc, updated_at desc, created_at asc, date_of_test asc, priority desc, case_number asc, status_index_contact_tracing asc, status_contacts_informed asc, is_approved asc)
;
        ");
    }
}
