<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MessageAddMailTemplateType extends Migration
{
    public function up(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->string('mail_template_type')->after('mail_template')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('message', static function (Blueprint $table): void {
            $table->dropColumn('mail_template_type');
        });
    }
}
