<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCaseList extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('case_list', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('name', 100);
            $table->uuid('organisation_uuid');
            $table->tinyInteger('is_queue', false, true)->default(0);
            $table->tinyInteger('is_default', false, true)->default(0);

            $table->foreign('organisation_uuid')->references('uuid')
                ->on('organisation')
                ->cascadeOnDelete();
        });


        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->uuid('assigned_case_list_uuid')->after('assigned_uuid')->nullable();
            $table->foreign('assigned_case_list_uuid')->references('uuid')
                ->on('case_list')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('assigned_case_list_uuid');
            $table->dropForeign('assigned_case_list_uuid');
        });

        Schema::drop('case_list');
    }
}
