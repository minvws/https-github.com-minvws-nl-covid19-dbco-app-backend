<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIdentityRequiredToMessage extends Migration
{
    public function up(): void
    {
        Schema::table('mail_template', static function (Blueprint $table): void {
            $table->boolean('identity_required')->default(false);
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->boolean('smm_identity_required');
        });
    }

    public function down(): void
    {
        Schema::table('mail_template', static function (Blueprint $table): void {
            $table->dropColumn('identity_required');
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('smm_identity_required');
        });
    }
}
