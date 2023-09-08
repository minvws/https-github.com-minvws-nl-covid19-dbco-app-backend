<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * The current migration for the organisation outsource table already adds this primary key, however this migration
 * fails on the non-dev environments without the primary key so it has been altered to add the primary key immediately.
 * However there might be some dev environments that already ran the other migration so this migration makes sure
 * all environments have the proper primary key.
 */
class AddPkToOrganisationOutsource extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $info = DB::selectOne("
            SELECT EXISTS(
              SELECT 1
              FROM information_schema.columns
              WHERE table_name = 'organisation_outsource'
              AND column_key = 'PRI'
            ) AS hasPrimaryKey
        ");

        if ($info->hasPrimaryKey) {
            return;
        }

        Schema::table('organisation_outsource', static function (Blueprint $table): void {
            $table->primary(['organisation_uuid', 'outsources_to_organisation_uuid'], 'pk_organisation_outsource');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // don't want to reverse this migration
    }
}
