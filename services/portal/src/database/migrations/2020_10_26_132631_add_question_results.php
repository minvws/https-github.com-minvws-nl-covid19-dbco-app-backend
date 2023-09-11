<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddQuestionResults extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // api wise we have task -> questionnaire result -> answers but since
        // questionnaireresult doesn't have any fields but a questionnaire id, we
        // don't create it at the db level (we can add questionnaire id on the task level)
        Schema::create('answer', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->uuid('task_uuid');
            $table->foreign('task_uuid')->references('uuid')
                ->on('task')
                ->onDelete('cascade');

            $table->uuid('question_uuid');
            $table->foreign('question_uuid')->references('uuid')
                ->on('question'); // no cascade: ensure we don't delete questionnaires that have pending results

            // Simple value answer object fields
            $table->string('spv_value')->nullable();

            // Contact details answer object fields
            $table->string('ctd_firstname')->nullable();
            $table->string('ctd_lastname')->nullable();
            $table->string('ctd_email')->nullable();
            $table->string('ctd_phonenumber')->nullable();

            // Classification details answer object fields
            $table->boolean('cfd_livedtogetherrisk')->nullable();
            $table->boolean('cfd_durationrisk')->nullable();
            $table->boolean('cfd_distancerisk')->nullable();
            $table->boolean('cfd_otherrisk')->nullable();

            $table->timestamps();
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->string('questionnaire_uuid')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('questionnaire_uuid');
        });

        Schema::dropIfExists('answer');
    }
}
