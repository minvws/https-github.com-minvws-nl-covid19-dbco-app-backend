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
        DB::table('risk_profile')
            ->where('risk_profile_enum', 'has_symptoms')
            ->update(['name' => 'Symptomatische index standaard']);

        DB::table('risk_profile')
            ->where('risk_profile_enum', 'no_symptoms')
            ->update(['name' => 'Asymptomatische index standaard']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Not needed as old values will not be used
    }
};
