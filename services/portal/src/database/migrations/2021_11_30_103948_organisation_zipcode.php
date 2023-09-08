<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OrganisationZipcode extends Migration
{
    public function up(): void
    {
        Schema::create('zipcode', static function (Blueprint $table): void {
            $table->char('zipcode', 6)->primary();
            $table->uuid('organisation_uuid');
            $table->foreign('organisation_uuid')->references('uuid')->on('organisation');
        });
    }

    public function down(): void
    {
        Schema::drop('zipcode');
    }
}
