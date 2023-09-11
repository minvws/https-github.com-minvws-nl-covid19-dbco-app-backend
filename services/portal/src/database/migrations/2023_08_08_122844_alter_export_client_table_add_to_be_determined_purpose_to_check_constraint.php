<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        try {
            DB::statement('ALTER TABLE export_client_purpose DROP CONSTRAINT chk_export_client_purpose_purpose');
        } catch (Throwable) {
            Log::info('Constraint chk_export_client_purpose_purpose does not exist, skipping');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
