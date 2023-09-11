<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTaskHpzoneFk extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->string('export_id')
                ->nullable();

            $table->dateTime('exported_at')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('export_id');
            $table->dropColumn('exported_at');
        });
    }
}
