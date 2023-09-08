<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseOrganisationLabels extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('assigned_organisation_label', 255)->nullable()->after('assigned_organisation_uuid');
            $table->string('organisation_label', 255)->nullable()->after('organisation_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('assigned_organisation_label');
            $table->dropColumn('organisation_label');
        });
    }
}
