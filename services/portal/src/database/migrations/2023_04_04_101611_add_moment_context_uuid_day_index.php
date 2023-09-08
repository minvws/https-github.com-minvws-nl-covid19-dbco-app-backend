<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('moment', static function (Blueprint $table): void {
            $table->index(['context_uuid', 'day'], 'moment_context_uuid_day_index');
        });
    }

    public function down(): void
    {
        Schema::table('moment', static function (Blueprint $table): void {
            $table->dropIndex('moment_context_uuid_day_index');
        });
    }
};
