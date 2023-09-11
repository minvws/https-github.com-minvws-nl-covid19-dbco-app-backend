<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->dateTime('date_of_result')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->dropColumn('date_of_result');
        });
    }
};
