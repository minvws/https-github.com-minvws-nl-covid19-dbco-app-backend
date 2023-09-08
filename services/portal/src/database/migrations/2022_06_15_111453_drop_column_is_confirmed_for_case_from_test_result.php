<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class DropColumnIsConfirmedForCaseFromTestResult extends Migration
{
    public function up(): void
    {
        DB::statement('alter table `test_result` drop column `is_confirmed_for_case`;');
    }

    public function down(): void
    {
        DB::statement('alter table `test_result` add `is_confirmed_for_case` tinyint(1) not null default 0;');
    }
}
