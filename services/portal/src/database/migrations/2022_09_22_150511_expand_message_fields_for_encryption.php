<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropIndex('message_smm_uuid_index');
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->text('mailer_identifier')->change();
            $table->text('from_name')->change();
            $table->text('from_email')->change();
            $table->text('to_name')->change();
            $table->text('to_email')->change();
            $table->text('telephone')->change();
            $table->text('subject')->change();
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->index('smm_uuid', 'message_smm_uuid_index');
        });

        Schema::table('message', static function (Blueprint $table): void {
            $table->string('mailer_identifier')->change();
        });
    }
};
