<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('policy_variant')
            ->where('identifier', 'symptomatic')
            ->update([
                'name' => 'Symptomatisch - Standaard',
                'sort_order' => 10,
            ]);

        DB::table('policy_variant')
            ->where('identifier', 'symptomatic_extended')
            ->update([
                'name' => 'Symptomatisch - Verlengd',
                'sort_order' => 20,
            ]);

        DB::table('policy_variant')
            ->where('identifier', 'asymptomatic')
            ->update([
                'name' => 'Asymptomatisch - Standaard',
                'sort_order' => 30,
            ]);

        DB::table('policy_variant')
            ->where('identifier', 'asymptomatic_extended')
            ->update([
                'name' => 'Asymptomatisch - Verlengd',
                'sort_order' => 40,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not needed as old values will not be used
    }
};
