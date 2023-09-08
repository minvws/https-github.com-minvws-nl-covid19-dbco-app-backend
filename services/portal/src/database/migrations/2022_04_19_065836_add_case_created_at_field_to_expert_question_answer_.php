<?php

declare(strict_types=1);

use App\Models\Eloquent\ExpertQuestionAnswer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCaseCreatedAtFieldToExpertQuestionAnswer extends Migration
{
    public function up(): void
    {
        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->timestamp('case_created_at')->after('uuid')->nullable(); // Make nullable to make sure migration does not fail
        });

        DB::update("
            UPDATE expert_question_answer
            SET case_created_at = (
                SELECT case_created_at
                FROM expert_question
                WHERE expert_question.answer_uuid = expert_question_answer.uuid
            )
        ");
    }

    public function down(): void
    {
        $expertQuestionAnswers = ExpertQuestionAnswer::all();

        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->dropColumn('case_created_at');
        });

        foreach ($expertQuestionAnswers as $expertQuestionAnswer) {
            unset($expertQuestionAnswer->case_created_at);
            $expertQuestionAnswer->save();
        }
    }
}
