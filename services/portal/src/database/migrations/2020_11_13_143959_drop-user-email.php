<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUserEmail extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bcouser', static function (Blueprint $table): void {
            $table->dropColumn('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bcouser', static function (Blueprint $table): void {
            $table->string('email')->nullable();
        });
    }
}
