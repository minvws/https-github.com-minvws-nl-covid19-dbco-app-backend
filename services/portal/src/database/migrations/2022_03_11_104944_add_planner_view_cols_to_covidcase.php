<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddPlannerViewColsToCovidcase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                ADD organisation_planner_view VARCHAR(20) NOT NULL,
                ADD current_organisation_planner_view VARCHAR(20) NOT NULL,
                ADD case_list_planner_view VARCHAR(20) NOT NULL,
                DROP INDEX i_covidcase_case_list_stats,
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_corg,
                DROP INDEX i_covidcase_planner_aorg,
                DROP INDEX i_covidcase_planner_cl,
                ADD INDEX i_covidcase_aorg (assigned_organisation_uuid),
                ADD INDEX i_covidcase_planner_org (organisation_uuid, organisation_planner_view, assigned_case_list_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, current_organisation_planner_view, organisation_uuid, assigned_case_list_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, case_list_planner_view, current_organisation_uuid, organisation_uuid, assigned_user_uuid, updated_at, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed)
        ");

        DB::statement("DROP FUNCTION IF EXISTS case_planner_view");

        // NOTE:
        // The explicit character sets are needed because they need to match with the table to be
        // able to use the parameters in queries.
        DB::statement("
            CREATE FUNCTION case_planner_view (
                type ENUM('organisation', 'current_organisation', 'case_list'),
                assigned_organisation_uuid CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                assigned_case_list_uuid CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                assigned_user_uuid CHAR(36) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                bco_status VARCHAR(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
                is_approved TINYINT,
                deleted_at TIMESTAMP
            ) RETURNS VARCHAR(20) READS SQL DATA
            BEGIN
                DECLARE in_queue INT DEFAULT 0;

                IF deleted_at IS NOT NULL
                THEN
                    RETURN 'unsupported';
                END IF;

                IF type = 'case_list' AND assigned_case_list_uuid IS NULL
                THEN
                    RETURN 'unsupported';
                END IF;

                IF type = 'organisation' AND assigned_organisation_uuid IS NOT NULL
                THEN
                    RETURN 'outsourced';
                ELSEIF type = 'organisation'
                THEN
                    RETURN 'unsupported';
                END IF;

                IF type <> 'case_list' AND assigned_case_list_uuid IS NOT NULL
                THEN
                    SELECT cl.is_queue INTO in_queue
                    FROM case_list cl
                    WHERE cl.uuid = assigned_case_list_uuid;
                END IF;

                IF bco_status IN ('draft', 'open', 'completed') AND assigned_user_uuid IS NOT NULL
                THEN
                    RETURN 'assigned';
                ELSEIF bco_status IN ('draft', 'open', 'completed')
                    AND type <> 'case_list'
                    AND assigned_case_list_uuid IS NOT NULL
                    AND in_queue = 0
                THEN
                    RETURN 'assigned';
                ELSEIF bco_status IN ('draft', 'open', 'completed') AND in_queue = 1
                THEN
                    RETURN 'queued';
                ELSEIF bco_status = 'completed'
                    AND is_approved IS NULL
                    AND assigned_user_uuid IS NULL
                    AND (type = 'case_list' OR assigned_case_list_uuid IS NULL)
                THEN
                    RETURN 'completed';
                ELSEIF bco_status = 'archived'
                THEN
                    RETURN 'archived';
                ELSEIF bco_status IN ('draft', 'open') OR (bco_status = 'completed' AND is_approved = 0)
                THEN
                    RETURN 'unassigned';
                ELSE
                    RETURN 'unsupported';
                END IF;
            END;
        ");

        DB::statement("
            CREATE TRIGGER trg_covidcase_bi_pv
            BEFORE INSERT on covidcase
            FOR EACH ROW
            BEGIN
                SET NEW.organisation_planner_view = case_planner_view('organisation', NEW.assigned_organisation_uuid, NEW.assigned_case_list_uuid, NEW.assigned_user_uuid, NEW.bco_status, NEW.is_approved, NEW.deleted_at);
                SET NEW.current_organisation_planner_view = case_planner_view('current_organisation', NEW.assigned_organisation_uuid, NEW.assigned_case_list_uuid, NEW.assigned_user_uuid, NEW.bco_status, NEW.is_approved, NEW.deleted_at);
                SET NEW.case_list_planner_view = case_planner_view('case_list', NEW.assigned_organisation_uuid, NEW.assigned_case_list_uuid, NEW.assigned_user_uuid, NEW.bco_status, NEW.is_approved, NEW.deleted_at);
            END;
        ");

        DB::statement("
            CREATE TRIGGER trg_covidcase_bu_pv
            BEFORE UPDATE on covidcase
            FOR EACH ROW
            BEGIN
                SET NEW.organisation_planner_view = case_planner_view('organisation', NEW.assigned_organisation_uuid, NEW.assigned_case_list_uuid, NEW.assigned_user_uuid, NEW.bco_status, NEW.is_approved, NEW.deleted_at);
                SET NEW.current_organisation_planner_view = case_planner_view('current_organisation', NEW.assigned_organisation_uuid, NEW.assigned_case_list_uuid, NEW.assigned_user_uuid, NEW.bco_status, NEW.is_approved, NEW.deleted_at);
                SET NEW.case_list_planner_view = case_planner_view('case_list', NEW.assigned_organisation_uuid, NEW.assigned_case_list_uuid, NEW.assigned_user_uuid, NEW.bco_status, NEW.is_approved, NEW.deleted_at);
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TRIGGER trg_covidcase_bu_pv");
        DB::statement("DROP TRIGGER trg_covidcase_bi_pv");

        DB::statement("DROP FUNCTION IF EXISTS case_planner_view");

        DB::statement("
            ALTER TABLE covidcase
                DROP COLUMN organisation_planner_view,
                DROP COLUMN current_organisation_planner_view,
                DROP COLUMN case_list_planner_view,
                DROP INDEX i_covidcase_aorg,
                DROP INDEX i_covidcase_planner_org,
                DROP INDEX i_covidcase_planner_corg,
                DROP INDEX i_covidcase_planner_cl,
                ADD INDEX i_covidcase_planner_org (organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_aorg (assigned_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, bco_status, assigned_user_uuid, current_organisation_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test, priority DESC, case_number, status_index_contact_tracing, status_contacts_informed),
                ADD INDEX i_covidcase_case_list_stats (assigned_case_list_uuid, (IF(deleted_at IS NOT NULL, 1, 0)), bco_status, (IF(assigned_user_uuid IS NOT NULL, 1, 0)), is_approved)
        ");
    }
}
