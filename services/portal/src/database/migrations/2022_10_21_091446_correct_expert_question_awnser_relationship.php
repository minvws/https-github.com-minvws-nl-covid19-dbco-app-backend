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
        // Create the new column
        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->uuid('expert_question_uuid')->after('uuid')->nullable();

            $table->foreign('expert_question_uuid')
                ->on('expert_question')
                ->references('uuid')
                ->cascadeOnDelete();
        });

        // Set all the uuid's from the expert question inside the new column as references by the old column
        DB::statement('
            UPDATE expert_question_answer, expert_question
            SET expert_question_answer.expert_question_uuid = expert_question.uuid
            WHERE expert_question_answer.uuid = expert_question.answer_uuid;
        ');

        // Drop the old column
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->dropForeign('expert_question_answer_uuid_foreign');
            $table->dropColumn('answer_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate the old column
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->uuid('answer_uuid')->nullable();

            $table->foreign('answer_uuid')
                ->on('expert_question_answer')
                ->references('uuid');
        });

        // Set the values back
        DB::statement('
            UPDATE expert_question_answer, expert_question
            SET expert_question.answer_uuid = expert_question_answer.uuid
            WHERE expert_question.uuid = expert_question_answer.expert_question_uuid;
        ');

        // drop the previous column
        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->dropForeign('expert_question_answer_expert_question_uuid_foreign');
            $table->dropColumn('expert_question_uuid');
        });
    }
};
