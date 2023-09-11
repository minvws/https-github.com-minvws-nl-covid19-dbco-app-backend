<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->string('custom_type_of_test', 100)->after('type_of_test')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('test_result', static function (Blueprint $table): void {
            $table->dropColumn('custom_type_of_test');
        });
    }
};
