<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MailToMessage extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->renameColumn('mail_template_type', 'message_template_type');
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->renameColumn('message_template_type', 'mail_template_type');
        });
    }
}
