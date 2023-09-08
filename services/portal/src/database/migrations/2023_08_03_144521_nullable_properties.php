<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('UPDATE `risk_profile` SET `is_active` = 0 WHERE `is_active` IS NULL;');

        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->string('is_active')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('risk_profile', static function (Blueprint $table): void {
            $table->string('is_active')->nullable()->change();
        });
    }
};
