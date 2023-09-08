<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateArchivedCases extends Migration
{
    public function up(): void
    {
        DB::statement("
            UPDATE covidcase
            set assigned_user_uuid = NULL
            WHERE bco_status = 'archived'
        ");
    }

    public function down(): void
    {
        // Silence is gold...
    }
}
