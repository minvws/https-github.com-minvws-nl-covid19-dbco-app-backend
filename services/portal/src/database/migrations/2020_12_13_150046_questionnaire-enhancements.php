<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class QuestionnaireEnhancements extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('question', static function (Blueprint $table): void {
            $table->integer('sort_order')->default('0');
            $table->string('hpzone_fieldref')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('question', static function (Blueprint $table): void {
            $table->dropColumn('hpzone_fieldref');
            $table->dropColumn('sort_order');
        });
    }
}
