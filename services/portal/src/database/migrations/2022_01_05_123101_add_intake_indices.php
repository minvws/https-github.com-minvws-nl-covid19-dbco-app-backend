<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Add indices for intake and related data structures.
 */
class AddIntakeIndices extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // NOTE:
        // The last index doesn't have the organisation_uuid column because we need to match intakes across organisations.
        DB::statement("
            ALTER TABLE intake
                ADD INDEX i_intake_received_at (organisation_uuid, received_at DESC),
                ADD INDEX i_intake_date_of_symptom_onset (organisation_uuid, date_of_symptom_onset),
                ADD INDEX i_intake_date_of_test (organisation_uuid, date_of_test),
                ADD INDEX i_intake_cat1_count (organisation_uuid, cat1_count),
                ADD INDEX i_intake_estimated_cat2_count (organisation_uuid, estimated_cat2_count),
                ADD INDEX i_intake_identifier (identifier_type, identifier)
        ");

        // NOTE 1:
        // Although it looks like that reversing the columns in the i_covidcase_tmn_pbg index would
        // mean that we would not need the i_covidcase_pbg index (as an index can be used left to right), this
        // is not entirely true. We also need to support looking up by test_monster_number without a pseudo_bsn_guid.
        // Reversing the columns of the i_covidcase_tmn_pbg would mean that we always need to provide the pseudo_bsn_guid
        // in our query to be able to use the index. Hence, the extra index.
        //
        // NOTE 2:
        // We don't need to add the organisation_uuid column to these indices as we need to match any case across
        // organisations.
        DB::statement("
            ALTER TABLE covidcase
                ADD INDEX i_covidcase_pbg (pseudo_bsn_guid),
                ADD INDEX i_covidcase_tmn_pbg (test_monster_number, pseudo_bsn_guid)
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("
            ALTER TABLE covidcase
                DROP INDEX i_covidcase_tmn_pbg,
                DROP INDEX i_covidcase_pbg
        ");

        DB::statement("
            ALTER TABLE intake
                DROP INDEX i_intake_identifier,
                DROP INDEX i_intake_estimated_cat2_count,
                DROP INDEX i_intake_cat1_count,
                DROP INDEX i_intake_date_of_test,
                DROP INDEX i_intake_date_of_symptom_onset,
                DROP INDEX i_intake_received_at
        ");
    }
}
