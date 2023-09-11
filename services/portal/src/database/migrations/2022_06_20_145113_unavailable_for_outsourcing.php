<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UnavailableForOutsourcing extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement(
            "UPDATE organisation SET is_available_for_outsourcing = 0 WHERE type IN ('outsourceDepartment', 'outsourceOrganisation');",
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement(
            "UPDATE organisation SET is_available_for_outsourcing = 1 WHERE type IN ('outsourceDepartment', 'outsourceOrganisation');",
        );
    }
}
