<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddIndexToTestMonsterNumberColumn extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE covidcase ADD INDEX i_covidcase_test_monster_number (test_monster_number)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE covidcase DROP INDEX i_covidcase_test_monster_number');
    }
}
