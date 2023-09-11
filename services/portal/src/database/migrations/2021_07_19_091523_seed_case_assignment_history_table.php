<?php

declare(strict_types=1);

use App\Models\Eloquent\CaseAssignmentHistory;
use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class SeedCaseAssignmentHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('covidcase')->orderBy('created_at')->chunk(100, static function ($cases): void {
            foreach ($cases as $case) {
                CaseAssignmentHistory::create([
                    'covidcase_uuid' => $case->uuid,
                    'assigned_user_uuid' => $case->assigned_user_uuid,
                    'assigned_organisation_uuid' => $case->assigned_organisation_uuid,
                    'assigned_case_list_uuid' => $case->assigned_case_list_uuid,
                    'assigned_at' => CarbonImmutable::now(),
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to do here
    }
}
