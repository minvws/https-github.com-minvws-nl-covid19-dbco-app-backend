<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class SetDefaultForBcoPhase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $defaultBcoPhase = 'none';

        // Call the bco-phase purge command
        Artisan::call('bco-phase:replace', ['--to' => $defaultBcoPhase, '--force' => 1]);

        Schema::table('organisation', static function (Blueprint $table) use ($defaultBcoPhase): void {
            $table->string('bco_phase')->default($defaultBcoPhase)->nullable(false)->change();
        });

        Schema::table('covidcase', static function (Blueprint $table) use ($defaultBcoPhase): void {
            $table->string('bco_phase')->default($defaultBcoPhase)->nullable(false)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('bco_phase')->nullable()->default(null)->change();
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('bco_phase')->nullable()->default(null)->change();
        });
    }
}
