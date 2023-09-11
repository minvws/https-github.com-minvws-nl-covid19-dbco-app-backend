<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class OrganisationsWithActiveDirectory extends Migration
{
    public function up(): void
    {
        DB::statement("UPDATE organisation SET external_id = '99007', type = 'outsourceOrganisation' WHERE external_id = 'webhelp'");
        DB::statement(
            "UPDATE organisation SET external_id = '99005', type = 'outsourceOrganisation' WHERE external_id = 'teleperformance'",
        );
    }

    public function down(): void
    {
        DB::statement("UPDATE organisation SET external_id = 'webhelp', type = 'outsourceDepartment' WHERE external_id = '99007'");
        DB::statement("UPDATE organisation SET external_id = 'teleperformance', type = 'outsourceDepartment' WHERE external_id = '99005'");
    }
}
