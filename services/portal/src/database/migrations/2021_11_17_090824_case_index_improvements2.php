<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CaseIndexImprovements2 extends Migration
{
    public function up(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                ADD INDEX i_covidcase_planner_cl (assigned_case_list_uuid, bco_status, assigned_user_uuid, current_organisation_uuid, assigned_organisation_uuid, deleted_at, updated_at DESC, created_at, date_of_test)
        ");
    }

    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase DROP INDEX i_covidcase_planner_cl
        ");
    }
}
