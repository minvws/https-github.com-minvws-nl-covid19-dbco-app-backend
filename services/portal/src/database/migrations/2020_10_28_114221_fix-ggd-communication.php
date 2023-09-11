<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class FixGgdCommunication extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('task')
            ->where('communication', 'ggd')
            ->update([
                "communication" => "staff",
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // not relevant
    }
}
