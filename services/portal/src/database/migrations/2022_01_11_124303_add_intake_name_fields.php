<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIntakeNameFields extends Migration
{
    public function up(): void
    {
        Schema::table('intake', static function (Blueprint $table): void {
            $table->string('firstname')->nullable()->after('estimated_cat2_count');
            $table->string('prefix')->nullable()->after('firstname');
            $table->string('lastname')->nullable()->after('prefix');
        });
    }

    public function down(): void
    {
        Schema::table('intake', static function (Blueprint $table): void {
            $table->dropColumn('firstname');
            $table->dropColumn('prefix');
            $table->dropColumn('lastname');
        });
    }
}
