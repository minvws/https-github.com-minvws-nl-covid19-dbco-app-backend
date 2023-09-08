<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->string('result', 50)->after('type')->default('unknown');
        });

        DB::table('test_result')->update(['result' => 'positive']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->dropColumn('result');
        });
    }
};
