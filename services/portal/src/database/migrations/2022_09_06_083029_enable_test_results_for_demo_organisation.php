<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(
            "UPDATE `organisation` SET `hp_zone_code` = '000000', `is_allowed_to_report_test_results` = '1' WHERE `uuid` = '00000000-0000-0000-0000-000000000000'",
        );
    }

    public function down(): void
    {
        DB::statement(
            "UPDATE `organisation` SET `hp_zone_code` = NULL, `is_allowed_to_report_test_results` = '0' WHERE `uuid` = '00000000-0000-0000-0000-000000000000'",
        );
    }
};
