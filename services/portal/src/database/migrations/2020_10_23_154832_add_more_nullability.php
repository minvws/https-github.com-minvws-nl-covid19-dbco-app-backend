<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMoreNullability extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case', static function (Blueprint $table): void {
            // Fields should be nullable because draft cases don't have data yet.
            // oci integration has an error when making columns nullable, so we cheat by
            // dropping the column and recreating it. We don't have any data yet at this point.
            $table->dropColumn('name');
            $table->dropColumn('case_id');
        });

        Schema::table('case', static function (Blueprint $table): void {
            $table->string('name')->nullable();
            $table->string('case_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Can't add non-nullable columns if db is not empty. Keep 'm nullable
    }
}
