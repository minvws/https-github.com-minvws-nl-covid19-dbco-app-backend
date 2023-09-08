<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class PlaceAdminIndexes extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('
            ALTER TABLE place
                DROP INDEX i_place_search_label,
                DROP INDEX i_place_search_postalcode,
                DROP INDEX i_place_search_street,
                DROP INDEX i_place_search_town,
                ADD INDEX i_place_search (organisation_uuid, label, postalcode, street, is_verified DESC, created_at DESC)
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('
            ALTER TABLE place
                DROP INDEX i_place_search,
                ADD INDEX i_place_search_label (label, is_verified, created_at),
                ADD INDEX i_place_search_postalcode (postalcode, is_verified, created_at),
                ADD INDEX i_place_search_street (street, is_verified, created_at),
                ADD INDEX i_place_search_town (town, is_verified, created_at)
        ');
    }
}
