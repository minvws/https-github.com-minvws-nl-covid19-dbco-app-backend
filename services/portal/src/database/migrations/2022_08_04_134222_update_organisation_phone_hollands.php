<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateOrganisationPhoneHollands extends Migration
{
    public function up(): void
    {
        DB::table('organisation')
            ->where('external_id', '16003')
            ->update(['phone_number' => '085 - 078 28 78']);

        DB::table('organisation')
            ->where('external_id', '10003')
            ->update(['phone_number' => '088 - 0100 533']);
    }

    public function down(): void
    {
        DB::table('organisation')
            ->where('external_id', '16003')
            ->update(['phone_number' => '088 - 0100 533']);

        DB::table('organisation')
            ->where('external_id', '10003')
            ->update(['phone_number' => '088 - 010 05 62']);
    }
}
