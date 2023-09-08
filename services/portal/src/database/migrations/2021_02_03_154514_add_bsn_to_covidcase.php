<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBsnToCovidcase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('index_bsn')->nullable();
            $table->string('index_bsn_hash')->nullable();
            $table->integer('index_bsn_ends_with', false, true)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['index_bsn', 'index_bsn_hash', 'index_bsn_ends_with']);
        });
    }
}
