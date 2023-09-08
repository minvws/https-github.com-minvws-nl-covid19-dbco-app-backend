<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateEncryptedColumnsLength extends Migration
{
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->text('label')->change();
            $table->text('task_context')->change();
            $table->text('nature')->change();
        });
    }

    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->string('label')->change();
            $table->string('task_context')->change();
            $table->string('nature')->change();
        });
    }
}
