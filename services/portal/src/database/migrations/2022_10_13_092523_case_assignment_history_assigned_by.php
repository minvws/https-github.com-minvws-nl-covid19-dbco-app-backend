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
        Schema::table('case_assignment_history', static function (Blueprint $table): void {
            $table->string('assigned_by', 50)->nullable()->after('assigned_at');
            $table->foreign('assigned_by')->references('uuid')->on('bcouser')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('case_assignment_history', static function (Blueprint $table): void {
            $table->dropForeign('case_assignment_history_assigned_by_foreign');
            $table->dropColumn('assigned_by');
        });
    }
};
