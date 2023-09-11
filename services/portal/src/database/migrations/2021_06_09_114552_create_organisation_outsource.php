<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganisationOutsource extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('organisation_outsource', static function (Blueprint $table): void {
            $table->uuid('organisation_uuid');
            $table->uuid('outsources_to_organisation_uuid');
            $table->foreign('organisation_uuid')->references('uuid')
                ->on('organisation')
                ->cascadeOnDelete();
            $table->foreign('outsources_to_organisation_uuid')->references('uuid')
                ->on('organisation')
                ->cascadeOnDelete();
            $table->primary(['organisation_uuid', 'outsources_to_organisation_uuid'], 'pk_organisation_outsource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::drop('organisation_outsource');
    }
}
