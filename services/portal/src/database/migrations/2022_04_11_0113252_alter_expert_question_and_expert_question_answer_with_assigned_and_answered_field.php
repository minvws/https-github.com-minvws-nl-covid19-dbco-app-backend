<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlterExpertQuestionAndExpertQuestionAnswerWithAssignedAndAnsweredField extends Migration
{
    public function up(): void
    {
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->renameColumn('expert_user_uuid', 'assigned_user_uuid');
        });

        Schema::table('expert_question_answer', static function ($table): void {
            $table->string('answered_by')->after('answer')->nullable(); // Make nullable to make sure migration does not fail
            $table->foreign('answered_by')->references('uuid')->on('bcouser');
            $table->index('answered_by');
        });

        DB::update("
            UPDATE expert_question_answer
            SET answered_by = (
                SELECT assigned_user_uuid
                FROM expert_question
                WHERE answer_uuid = expert_question_answer.uuid
            )
        ");

        DB::update("
            UPDATE expert_question
            SET assigned_user_uuid = NULL
            WHERE answer_uuid IS NOT NULL
        ");

        Schema::table('expert_question_answer', static function ($table): void {
            $table->string('answered_by')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->renameColumn('assigned_user_uuid', 'expert_user_uuid');
        });

        Schema::table('expert_question_answer', static function ($table): void {
            $table->dropColumn('answered_by');
        });
    }
}
