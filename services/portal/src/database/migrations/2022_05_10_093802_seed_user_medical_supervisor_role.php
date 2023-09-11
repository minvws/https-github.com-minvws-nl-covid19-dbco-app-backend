<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class SeedUserMedicalSupervisorRole extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $organisationLs1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->first();

        if ($organisationLs1 !== null) {
            $userUserMedicalSupervisorGgd1 = EloquentUser::factory()->create([
                'name' => 'Demo GGD1 Gebruiker & Medische Supervisor',
                'uuid' => DummySeeder::DEMO_USER_MEDICAL_SUPERVISOR_UUID,
                'external_id' => DummySeeder::DEMO_USER_MEDICAL_SUPERVISOR_UUID,
                'roles' => 'user,medical_supervisor',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'consented_at' => CarbonImmutable::now(),
            ]);

            $organisationLs1->users()->saveMany([
                $userUserMedicalSupervisorGgd1,
            ]);
        }

        /** @var EloquentOrganisation $organisationTwo */
        $organisationTwo = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_TWO_UUID)->first();

        if ($organisationTwo === null) {
            return;
        }

        $userUserMedicalSupervisorGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Gebruiker & Medische Supervisor',
            'uuid' => DummySeeder::DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID,
            'external_id' => DummySeeder::DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID,
            'roles' => 'user,medical_supervisor',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $organisationTwo->users()->saveMany([
            $userUserMedicalSupervisorGgd2,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = EloquentUser::find(DummySeeder::DEMO_USER_MEDICAL_SUPERVISOR_UUID);
        if ($user instanceof EloquentUser) {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID);
        if ($user instanceof EloquentUser) {
            $user->delete();
        }
    }
}
