<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeQuestionTypeTextToOpen extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('question')
            ->where('question_type', 'text')
            ->update(["question_type" => 'open']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('question')
            ->where('question_type', 'open')
            ->update(["question_type" => 'text']);
    }
}
