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
        Schema::table('assignment', static function (Blueprint $table): void {
            $table->dropForeign('assignment_chore_uuid_foreign');

            $table->foreign('chore_uuid')->references('uuid')->on('chore')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assignment', static function (Blueprint $table): void {
            $table->dropForeign('assignment_chore_uuid_foreign');

            $table->foreign('chore_uuid')->references('uuid')->on('chore');
        });
    }
};
