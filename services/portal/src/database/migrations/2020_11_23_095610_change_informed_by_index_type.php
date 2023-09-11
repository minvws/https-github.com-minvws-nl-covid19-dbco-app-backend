<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ChangeInformedByIndexType extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // can't change type without db layer complaining,
        // as we aren't storing critical data yet, just drop and re-create

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('informed_by_index');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->integer('informed_by_index')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // can't change type without db layer complaining,
        // as we aren't storing critical data yet, just drop and re-create

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('informed_by_index');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->boolean('informed_by_index')->default(false);
        });
    }
}
