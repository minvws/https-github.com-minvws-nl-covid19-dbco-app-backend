<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('osiris_notification', static function (Blueprint $table): void {
            $table->index(['case_uuid', 'notified_at']);
        });
    }

    public function down(): void
    {
        Schema::table('osiris_notification', static function (Blueprint $table): void {
            $table->index('case_uuid');
            $table->dropIndex(['case_uuid', 'notified_at']);
        });
    }
};
