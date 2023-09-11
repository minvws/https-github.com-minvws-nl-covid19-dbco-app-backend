<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateOrganisationPhone extends Migration
{
    public function up(): void
    {
        DB::table('organisation')
            ->where('external_id', '11003')
            ->update(['phone_number' => '075 - 651 8388']);
    }

    public function down(): void
    {
        DB::table('organisation')
            ->where('external_id', '11003')
            ->update(['phone_number' => '075 - 651 83 20']);
    }
}
