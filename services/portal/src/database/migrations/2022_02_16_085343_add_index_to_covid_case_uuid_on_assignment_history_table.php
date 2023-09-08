<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexToCovidCaseUuidOnAssignmentHistoryTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case_assignment_history', static function (Blueprint $table): void {
            $table->index('covidcase_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_assignment_history', static function (Blueprint $table): void {
            $table->dropIndex('covidcase_uuid');
        });
    }
}
