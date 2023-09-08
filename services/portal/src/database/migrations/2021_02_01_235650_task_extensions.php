<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class TaskExtensions extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->string('task_group')->nullable()->default('contact');
            $table->foreignUuid('contact_case_uuid')->nullable();
            $table->integer('is_source')->nullable()->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('is_source');
            $table->dropColumn('contact_case_uuid');
            $table->dropColumn('task_group');
        });
    }
}
