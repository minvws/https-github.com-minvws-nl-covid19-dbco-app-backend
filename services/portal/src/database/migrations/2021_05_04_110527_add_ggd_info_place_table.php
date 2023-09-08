<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGgdInfoPlaceTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->string('ggd_code')->nullable();
            $table->string('ggd_municipality')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->dropColumn(['ggd_code', 'ggd_municipality']);
        });
    }
}
