<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CovidcaseAssignChanges extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->renameColumn('assigned_uuid', 'assigned_user_uuid');
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->uuid('assigned_organisation_uuid')->after('assigned_user_uuid')->nullable();
            $table->foreign('assigned_organisation_uuid')->references('uuid')
                ->on('organisation')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('assigned_organisation_uuid');
            $table->dropForeign('assigned_organisation_uuid');
            $table->renameColumn('assigned_user_uuid', 'assigned_uuid');
        });
    }
}
