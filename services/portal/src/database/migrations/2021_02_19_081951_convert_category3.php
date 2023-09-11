<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;

class ConvertCategory3 extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('task')
            ->where('category', '3')
            ->update([
                "category" => '3b',
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('task')
            ->where('category', '3b')
            ->update([
                "category" => '3',
            ]);
    }
}
