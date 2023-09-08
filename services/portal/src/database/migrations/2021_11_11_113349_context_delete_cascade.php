<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ContextDeleteCascade extends Migration
{
    public function up(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->dropForeign(['covidcase_uuid']);
            $table->foreign(['covidcase_uuid'])
                ->references('uuid')
                ->on('covidcase')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->dropForeign(['covidcase_uuid']);
            $table->foreign(['covidcase_uuid'])
                ->references('uuid')
                ->on('covidcase')
                ->restrictOnDelete();
        });
    }
}
