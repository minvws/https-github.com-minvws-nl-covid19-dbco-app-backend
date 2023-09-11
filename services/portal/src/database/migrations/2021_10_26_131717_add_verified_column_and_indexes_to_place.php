<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddVerifiedColumnAndIndexesToPlace extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->boolean('is_verified')->default(false);
        });

        DB::statement("
            ALTER TABLE place
                ADD INDEX i_place_search_label (label, is_verified, created_at),
                ADD INDEX i_place_search_postalcode (postalcode, is_verified, created_at),
                ADD INDEX i_place_search_street (street, is_verified, created_at),
                ADD INDEX i_place_search_town (town, is_verified, created_at),
                ADD INDEX i_place_list (is_verified, created_at)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('place', static function (Blueprint $table): void {
            $table->dropColumn('is_verified');
        });

        DB::statement("
            ALTER TABLE place
                DROP INDEX i_place_search_label,
                DROP INDEX i_place_search_postalcode,
                DROP INDEX i_place_search_street,
                DROP INDEX i_place_search_town,
                DROP INDEX i_place_list
        ");
    }
}
