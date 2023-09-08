<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterBcoPhaseField extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('bco_phase')->nullable()->default(null)->change();
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('bco_phase')->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('bco_phase')->default('1a')->change();
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('bco_phase')->default('1a')->change();
        });
    }
}
