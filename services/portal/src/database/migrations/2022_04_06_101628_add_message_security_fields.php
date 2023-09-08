<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMessageSecurityFields extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->timestamp('case_created_at');
            $table->string('user_uuid')->nullable()->change();
            $table->string('mailer_identifier')->nullable()->change();
            $table->boolean('is_secure')->nullable(false);
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->foreign('user_uuid')->references('uuid')->on('bcouser')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('case_created_at');
            $table->string('user_uuid')->nullable(false)->change();
            $table->string('mailer_identifier')->nullable(false)->change();
            $table->dropColumn('is_secure');

            $table->dropForeign('message_user_uuid_foreign');
            $table->dropIndex('message_user_uuid_foreign');
        });
    }
}
