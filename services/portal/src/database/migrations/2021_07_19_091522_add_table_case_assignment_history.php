<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableCaseAssignmentHistory extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_assignment_history', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('covidcase_uuid');
            $table->uuid('assigned_user_uuid')->nullable();
            $table->uuid('assigned_organisation_uuid')->nullable();
            $table->uuid('assigned_case_list_uuid')->nullable();
            $table->timestamp('assigned_at');

            $table->foreign('covidcase_uuid')->references('uuid')->on('covidcase')
                ->cascadeOnDelete();
            $table->foreign('assigned_user_uuid')->references('uuid')->on('bcouser')
                ->cascadeOnDelete();
            $table->foreign('assigned_organisation_uuid')->references('uuid')->on('organisation')
                ->cascadeOnDelete();
            $table->foreign('assigned_case_list_uuid')->references('uuid')->on('case_list')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('case_assignment_history');
    }
}
