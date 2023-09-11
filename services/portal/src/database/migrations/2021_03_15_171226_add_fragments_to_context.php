<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFragmentsToContext extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('context', static function (Blueprint $table): void {
            $table->longText('circumstances')->nullable();
            $table->longText('contact')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['circumstances', 'contact']);
        });
    }
}
