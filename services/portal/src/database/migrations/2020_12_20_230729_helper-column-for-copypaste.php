<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class HelperColumnForCopypaste extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('task', static function (Blueprint $table): void {
            $table->dateTime('copied_at')->nullable();
        });

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('export_id')->nullable();
            $table->dateTime('exported_at')->nullable();
            $table->dateTime('copied_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn('copied_at');
            $table->dropColumn('exported_at');
            $table->dropColumn('export_id');
        });

        Schema::table('task', static function (Blueprint $table): void {
            $table->dropColumn('copied_at');
        });
    }
}
