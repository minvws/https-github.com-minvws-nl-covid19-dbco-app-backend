<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CaseTestMonsterNumber extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('test_monster_number', 16)->unique()->nullable()->after('date_of_test');
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('test_monster_number');
        });
    }
}
