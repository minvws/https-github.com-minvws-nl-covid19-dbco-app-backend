<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrganizationTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Small weirdness note: if we use the american spelling (organization) throughout the migration, the dropIfExists will silently fail and the table doesn't get dropped.
        Schema::create('organisation', static function (Blueprint $table): void {
            $table->uuid('uuid')->primary();
            $table->string('external_id')->unique();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('user_organisation', static function (Blueprint $table): void {
            $table->uuid('user_uuid');
            $table->uuid('organisation_uuid');
            $table->primary(['user_uuid', 'organisation_uuid']);

            $table->foreign('user_uuid')->references('uuid')
                ->on('bcouser')
                ->onDelete('cascade');

            $table->foreign('organisation_uuid')->references('uuid')
                ->on('organisation')
                ->onDelete('cascade');

            $table->timestamps();
        });

        // Ability to assign cases
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->uuid('organisation_uuid')->nullable();

            $table->foreign('organisation_uuid')->references('uuid')
                ->on('organisation');

            $table->uuid('assigned_uuid')->nullable();

            $table->foreign('assigned_uuid')->references('uuid')
                ->on('bcouser');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('covidcase', static function (Blueprint $table): void {
            $table->dropForeign('covidcase_organisation_uuid_foreign');
            $table->dropForeign('covidcase_assigned_uuid_foreign');
            $table->dropColumn('organisation_uuid');
            $table->dropColumn('assigned_uuid');
        });

        Schema::dropIfExists('user_organisation');
        Schema::dropIfExists('organisation');
    }
}
