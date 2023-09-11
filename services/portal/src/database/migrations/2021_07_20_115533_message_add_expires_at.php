<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MessageAddExpiresAt extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->timestamp('smm_expires_at')->nullable()->after('smm_notification_sent_at');
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('smm_expires_at');
        });
    }
}
