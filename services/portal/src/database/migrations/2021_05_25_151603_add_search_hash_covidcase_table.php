<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

final class AddSearchHashCovidcaseTable extends Migration
{
    public function up(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('search_date_of_birth')->nullable();
            $table->string('search_email')->nullable();
            $table->string('search_phone')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropColumn(['search_date_of_birth', 'search_email', 'search_phone']);
        });
    }
}
