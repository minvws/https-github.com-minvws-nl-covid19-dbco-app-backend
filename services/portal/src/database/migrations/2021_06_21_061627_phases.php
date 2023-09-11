<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Phases extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('bco_phase')->default('1a');
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('bco_phase')->default('1a');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropColumn(['bco_phase']);
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['bco_phase']);
        });
    }
}
