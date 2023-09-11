<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * @see \App\Console\Commands\Migration\HydrateEpisodeStartDate initial hydration of `episode_start_date` column
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->date('episode_start_date')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('episode_start_date');
        });
    }
};
