<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateExpertQuestionAnswerTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('expert_question_answer', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('answer');
            $table->timestamps();
        });

        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->uuid('answer_uuid')->nullable();
            $table->foreign('answer_uuid')->references('uuid')->on('expert_question_answer');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expert_question', static function (Blueprint $table): void {
            $table->dropForeign('answer_uuid');
            $table->dropColumn('answer_uuid');
        });

        Schema::dropIfExists('expert_question_answer');
    }
}
