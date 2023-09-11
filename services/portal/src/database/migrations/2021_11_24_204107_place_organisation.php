<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class PlaceOrganisation extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->uuid('organisation_uuid')->nullable()->after('uuid');

            $table->foreign('organisation_uuid')->references('uuid')
                ->on('organisation');
        });

        DB::statement("
            ALTER TABLE place
                DROP INDEX i_place_list,
                ADD INDEX i_place_list (organisation_uuid, is_verified DESC, created_at DESC)
        ");

        DB::statement('
                UPDATE place SET organisation_uuid = (
                    SELECT covidcase.organisation_uuid
                    FROM covidcase
                    JOIN context ON covidcase.uuid = context.covidcase_uuid
                    WHERE context.place_uuid = place.uuid
                    ORDER BY context.created_at
                    LIMIT 0,1
        )');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->dropIndex('i_place_list');
            $table->dropForeign(['organisation_uuid']);
            $table->dropColumn(['organisation_uuid']);
        });
    }
}
