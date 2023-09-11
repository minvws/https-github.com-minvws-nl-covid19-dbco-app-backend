<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CaseFragmentColumns extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->longText('index')->nullable();
            $table->longText('contact')->nullable();
            $table->longText('alternate_contact')->nullable();
            $table->longText('alternative_language')->nullable();
            $table->longText('deceased')->nullable();
            $table->longText('call')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['index', 'contact', 'alternate_contact', 'alternative_language', 'deceased', 'call']);
        });
    }
}
