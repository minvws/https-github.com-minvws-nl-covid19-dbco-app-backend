<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCasePriority extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->integer('priority')->nullable(false)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('priority');
        });
    }
}
