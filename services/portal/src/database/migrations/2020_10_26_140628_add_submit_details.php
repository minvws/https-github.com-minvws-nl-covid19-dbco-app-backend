<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubmitDetails extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('case', static function (Blueprint $table): void {
            $table->datetime('index_submitted_at')->nullable();
            $table->datetime('seen_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case', static function (Blueprint $table): void {
            $table->dropColumn('seen_at');
            $table->dropColumn('index_submitted_at');
        });
    }
}
