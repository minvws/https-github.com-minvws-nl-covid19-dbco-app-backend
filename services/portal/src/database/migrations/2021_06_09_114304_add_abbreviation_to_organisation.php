<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAbbreviationToOrganisation extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('abbreviation', 5)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organisation', static function (Blueprint $table): void {
            $table->dropColumn('abbreviation');
        });
    }
}
