<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class SeedCallcenterUser extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get GGD organisation 1.
        $organisationGgd1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->first();

        if ($organisationGgd1 !== null) {
            $userCallcenterGgd1 = EloquentUser::factory()->create([
                'name' => 'Demo GGD1 Callcenter',
                'uuid' => DummySeeder::DEMO_CALLCENTER_UUID,
                'external_id' => DummySeeder::DEMO_CALLCENTER_UUID,
                'roles' => 'callcenter',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'consented_at' => CarbonImmutable::now(),
            ]);

            // Save user to organisation.
            $organisationGgd1->users()->saveMany([
                $userCallcenterGgd1,
            ]);
        }

        // Get GGD organisation 2.
        $organisationGgd2 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_TWO_UUID)->first();

        if ($organisationGgd2 === null) {
            return;
        }

        $userCallcenterGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Callcenter',
            'uuid' => DummySeeder::DEMO_TWO_CALLCENTER_UUID,
            'external_id' => DummySeeder::DEMO_TWO_CALLCENTER_UUID,
            'roles' => 'callcenter',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        // Save user to organisation.
        $organisationGgd2->users()->saveMany([
            $userCallcenterGgd2,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $userCallcenterGgd2 = EloquentUser::find(DummySeeder::DEMO_TWO_CALLCENTER_UUID);
        if ($userCallcenterGgd2 instanceof EloquentUser && $userCallcenterGgd2->roles === 'callcenter') {
            $userCallcenterGgd2->delete();
        }

        $userCallcenterGgd1 = EloquentUser::find(DummySeeder::DEMO_CALLCENTER_UUID);
        if ($userCallcenterGgd1 instanceof EloquentUser && $userCallcenterGgd1->roles === 'callcenter') {
            $userCallcenterGgd1->delete();
        }
    }
}
