<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('osiris_notification', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('case_uuid');
            $table->dateTime('notified_at');
            $table->string('bco_status');
            $table->string('osiris_status');
            $table->integer('osiris_questionnaire_version');

            $table->foreign('case_uuid')->references('uuid')->on('covidcase')->cascadeOnDelete();
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['osiris_last_notified_at']);
            $table->dropColumn('osiris_questionnaire_version');
        });

        DB::statement('ALTER TABLE `covidcase` ADD INDEX `i_covidcase_created_at_updated_at` (`created_at` DESC, `updated_at` DESC)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE `covidcase` DROP INDEX `i_covidcase_created_at_updated_at`');

        Schema::dropIfExists('osiris_notification');

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dateTime('osiris_last_notified_at')->nullable();
            $table->integer('osiris_questionnaire_version')->nullable();
        });
    }
};
