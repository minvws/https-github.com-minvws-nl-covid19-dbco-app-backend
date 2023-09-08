<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIndexToEvent extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE event ADD INDEX i_event_export_uuid_created_at (export_uuid, created_at)");
    }

    public function down(): void
    {
        // won't let us do this because mysql will use the index for the fk, but not really useful anyway
    }
}
