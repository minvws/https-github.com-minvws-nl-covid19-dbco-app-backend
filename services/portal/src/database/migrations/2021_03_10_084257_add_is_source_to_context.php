<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsSourceToContext extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->integer('is_source')->nullable()->default(0)->after('detailed_explanation');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->dropColumn('is_source');
        });
    }
}
