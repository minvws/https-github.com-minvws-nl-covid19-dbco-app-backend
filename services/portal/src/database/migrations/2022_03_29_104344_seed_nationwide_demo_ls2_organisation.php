<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class SeedNationwideDemoLs2Organisation extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get GGD organisation 1 & 2.
        $organisationGgd1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->first();
        $organisationGgd2 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_TWO_UUID)->first();
        // Get outsource organisation LS2.
        $organisationLs2 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_OUTSOURCE_ORGANISATION_TWO_UUID)->first();

        // Attach outsource organisation LS2 to GGD1 & GGD2.
        if (($organisationGgd1 === null) || ($organisationGgd2 === null) || ($organisationLs2 === null)) {
            return;
        }

        $organisationGgd1->outsourceOrganisations()->save($organisationLs2);
        $organisationGgd2->outsourceOrganisations()->save($organisationLs2);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Get GGD organisation 1 & 2.
        $organisationGgd1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->first();
        $organisationGgd2 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_TWO_UUID)->first();
        // Get outsource organisation LS2.
        $organisationLs2 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_OUTSOURCE_ORGANISATION_TWO_UUID)->first();

        // Detach outsource organisation LS2 from GGD1 & GGD2.
        if (($organisationGgd1 === null) || ($organisationGgd2 === null) || ($organisationLs2 === null)) {
            return;
        }

        $organisationGgd2->outsourceOrganisations()->detach($organisationLs2->uuid);
        $organisationGgd1->outsourceOrganisations()->detach($organisationLs2->uuid);
    }
}
