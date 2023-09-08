<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateDemoOrganisationType extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('organisation')
            ->where('type', '=', 'demo')
            ->where('external_id', '=', 'demo-ls1')
            ->update(['type' => 'outsourceOrganisation']);

        DB::table('organisation')
            ->where('type', '=', 'demo')
            ->update(['type' => 'regionalGGD']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
