<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateIntakeOrganisationFk extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //Adding ON DELETE CASECADE
        DB::statement("
            ALTER TABLE intake
                DROP FOREIGN KEY intake_organisation_uuid_foreign
        ");
        DB::statement("
            ALTER TABLE intake
                ADD CONSTRAINT intake_organisation_uuid_foreign FOREIGN KEY (organisation_uuid) REFERENCES organisation(uuid) ON DELETE CASCADE;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //Removing ON DELETE CASECADE
        DB::statement("
            ALTER TABLE intake
                DROP FOREIGN KEY intake_organisation_uuid_foreign
        ");
        DB::statement("
            ALTER TABLE intake
                ADD CONSTRAINT intake_organisation_uuid_foreign FOREIGN KEY (organisation_uuid) REFERENCES organisation(uuid);
        ");
    }
}
