<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DemoOrganisationPhoneNumber extends Migration
{
    public function up(): void
    {
        DB::table('organisation')
            ->where('uuid', '00000000-0000-0000-0000-000000000000')
            ->update([
                'phone_number' => '0123 4567',
            ]);
    }

    public function down(): void
    {
        DB::table('organisation')
            ->where('uuid', '00000000-0000-0000-0000-000000000000')
            ->update([
                'phone_number' => null,
            ]);
    }
}
