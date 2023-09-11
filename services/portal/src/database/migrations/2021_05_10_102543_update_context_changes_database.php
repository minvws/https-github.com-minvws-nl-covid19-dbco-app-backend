<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateContextChangesDatabase extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('place')
            ->where('category', 'anders')
            ->update(['category' => 'overige_andere_werkplek']);

        DB::table('place')
            ->where('category', 'beroepsonderwijs')
            ->update(['category' => 'mbo']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nothing to do here
    }
}
