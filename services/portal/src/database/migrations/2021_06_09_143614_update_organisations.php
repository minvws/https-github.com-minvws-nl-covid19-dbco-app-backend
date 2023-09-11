<?php

declare(strict_types=1);

use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class UpdateOrganisations extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Artisan::call('db:seed', ['--class' => 'OrganisationSeeder', '--force' => true]);

        DB::table('organisation')
            ->where('type', 'unknown')
            ->whereNotIn(
                'uuid',
                [DummySeeder::DEMO_ORGANISATION_UUID, DummySeeder::DEMO_OUTSOURCE_ORGANISATION_UUID, DummySeeder::DEMO_ORGANISATION_TWO_UUID],
            )
            ->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
}
