<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class InformedByFields extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('informed_by_index');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->datetime('informed_by_index_at')->nullable()->after('exported_at');
            $table->datetime('informed_by_staff_at')->nullable()->after('informed_by_index_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('informed_by_index_at');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->integer('informed_by_index')->default(0);
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn(['informed_by_staff_at']);
        });
    }
}
