<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE `risk_profile` MODIFY COLUMN `name` VARCHAR(255) AFTER `policy_guideline_uuid`");
        DB::statement("ALTER TABLE `risk_profile` MODIFY COLUMN `risk_profile_enum` VARCHAR(255) AFTER `name`");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `risk_profile` MODIFY COLUMN `name` VARCHAR(255) AFTER `uuid`");
        DB::statement("ALTER TABLE `risk_profile` MODIFY COLUMN `risk_profile_enum` VARCHAR(255) AFTER `name`");
    }
};
