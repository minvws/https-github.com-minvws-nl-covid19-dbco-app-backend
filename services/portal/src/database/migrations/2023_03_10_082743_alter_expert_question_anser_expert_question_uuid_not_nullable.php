<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('
            DELETE FROM expert_question_answer
            WHERE expert_question_uuid IS NULL
        ');

        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->dropForeign('expert_question_answer_expert_question_uuid_foreign');
        });

        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->string('expert_question_uuid')->nullable(false)->change();

            $table->foreign('expert_question_uuid')
                ->on('expert_question')
                ->references('uuid')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->dropForeign('expert_question_answer_expert_question_uuid_foreign');
        });

        Schema::table('expert_question_answer', static function (Blueprint $table): void {
            $table->string('expert_question_uuid')->nullable(true)->change();

            $table->foreign('expert_question_uuid')
                ->on('expert_question')
                ->references('uuid')
                ->cascadeOnDelete();
        });
    }
};
