<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddSchemaVersionToTask extends Migration
{
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->unsignedInteger('schema_version')->nullable(true);
        });

        DB::table('task')->whereNull('schema_version')->update(['schema_version' => 1]);

        Schema::table('task', static function (Blueprint $table): void {
            $table->unsignedInteger('schema_version')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('schema_version');
        });
    }
}
