<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('policy_versions', static function (Blueprint $table): void {
            $table->renameColumn('started_at', 'start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('policy_versions', static function (Blueprint $table): void {
            $table->renameColumn('start_date', 'started_at');
        });
    }
};
