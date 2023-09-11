<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->index('deleted_at', 'i_covidcase_deleted_at');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->index('deleted_at', 'i_task_deleted_at');
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropIndex('i_covidcase_deleted_at');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropIndex('i_task_deleted_at');
        });
    }
};
