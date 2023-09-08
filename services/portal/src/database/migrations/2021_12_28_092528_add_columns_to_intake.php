<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnsToIntake extends Migration
{
    public function up(): void
    {
        Schema::table('intake', static function (Blueprint $table): void {
            $table->string('pc3', 3);
            $table->string('gender', 50);
        });
    }

    public function down(): void
    {
        Schema::table('intake', static function (Blueprint $table): void {
            $table->dropColumn('pc3');
            $table->dropColumn('gender');
        });
    }
}
