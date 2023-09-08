<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSortOrderToAnswerOption extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('answer_option', static function (Blueprint $table): void {
            $table->unsignedInteger('sort_order')->default(0);
            $table->index(['question_uuid', 'sort_order'], 'i_answer_option_quso');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answer_option', static function (Blueprint $table): void {
            $table->dropIndex('i_answer_option_quso');
            $table->dropColumn('sort_order');
        });
    }
}
