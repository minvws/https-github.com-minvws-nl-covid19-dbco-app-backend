<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class CaseCurrentOrganisation extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->char('current_organisation_uuid', 36)->nullable()->after('organisation_uuid');
        });

        DB::statement("
            CREATE TRIGGER trg_covidcase_bi
            BEFORE INSERT on covidcase
            FOR EACH ROW SET NEW.current_organisation_uuid = COALESCE(NEW.assigned_organisation_uuid, NEW.organisation_uuid);
        ");

        DB::statement("
            CREATE TRIGGER trg_covidcase_bu
            BEFORE UPDATE on covidcase
            FOR EACH ROW SET NEW.current_organisation_uuid = COALESCE(NEW.assigned_organisation_uuid, NEW.organisation_uuid);
        ");

        DB::statement("
            UPDATE covidcase SET current_organisation_uuid = COALESCE(assigned_organisation_uuid, organisation_uuid);
        ");

        DB::statement("
            ALTER TABLE covidcase ADD INDEX i_covidcase_planner_corg (current_organisation_uuid, bco_status, assigned_case_list_uuid, assigned_user_uuid, deleted_at, updated_at DESC)
        ");
    }

    public function down(): void
    {
        DB::statement("DROP TRIGGER trg_covidcase_bu");
        DB::statement("DROP TRIGGER trg_covidcase_bi");
        Schema::dropColumns('covidcase', ['current_organisation_uuid']);
    }
}
