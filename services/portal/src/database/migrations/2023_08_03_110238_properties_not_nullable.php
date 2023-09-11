<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('organisation_uuid')->nullable(false)->change();
        });

        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('name')->nullable(false)->change();
        });

        Schema::enableForeignKeyConstraints();
    }

    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->string('organisation_uuid')->nullable()->change();
        });

        Schema::table('organisation', static function (Blueprint $table): void {
            $table->string('name')->nullable()->change();
        });
    }
};
