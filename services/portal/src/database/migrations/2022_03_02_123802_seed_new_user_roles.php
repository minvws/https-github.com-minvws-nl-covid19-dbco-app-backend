<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class SeedNewUserRoles extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get GGD nationwide organisation 1.
        $organisationLs1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_OUTSOURCE_ORGANISATION_UUID)->first();

        if ($organisationLs1 !== null) {
            $userMedicalSupervisorLs1 = EloquentUser::factory()->create([
                'name' => 'Demo LS1 Medische Supervisor',
                'uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID,
                'external_id' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID,
                'roles' => 'medical_supervisor_nationwide',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'consented_at' => CarbonImmutable::now(),
            ]);

            $userConversationCoachLs1 = EloquentUser::factory()->create([
                'name' => 'Demo LS1 Gesprekcoach',
                'uuid' => DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_UUID,
                'external_id' => DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_UUID,
                'roles' => 'conversation_coach_nationwide',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'consented_at' => CarbonImmutable::now(),
            ]);

            // Save user to organisation.
            $organisationLs1->users()->saveMany([
                $userMedicalSupervisorLs1,
                $userConversationCoachLs1,
            ]);
        }

        // Get GGD organisation 1.
        $organisationGgd1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->first();

        if ($organisationGgd1 !== null) {
            $userMedicalSupervisorGgd1 = EloquentUser::factory()->create([
                'name' => 'Demo GGD1 Medische Supervisor',
                'uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_UUID,
                'external_id' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_UUID,
                'roles' => 'medical_supervisor',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'consented_at' => CarbonImmutable::now(),
            ]);

            $userConversationCoachGgd1 = EloquentUser::factory()->create([
                'name' => 'Demo GGD1 Gesprekcoach',
                'uuid' => DummySeeder::DEMO_CONVERSATION_COACH_UUID,
                'external_id' => DummySeeder::DEMO_CONVERSATION_COACH_UUID,
                'roles' => 'conversation_coach',
                'created_at' => CarbonImmutable::now(),
                'updated_at' => CarbonImmutable::now(),
                'consented_at' => CarbonImmutable::now(),
            ]);

            // Save users to organisation.
            $organisationGgd1->users()->saveMany([
                $userMedicalSupervisorGgd1,
                $userConversationCoachGgd1,
            ]);
        }

        // Get GGD organisation 2.
        $organisationGgd2 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_TWO_UUID)->first();

        if ($organisationGgd2 === null) {
            return;
        }

        $userMedicalSupervisorGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Medische Supervisor',
            'uuid' => DummySeeder::DEMO_TWO_MEDICAL_SUPERVISOR_UUID,
            'external_id' => DummySeeder::DEMO_TWO_MEDICAL_SUPERVISOR_UUID,
            'roles' => 'medical_supervisor',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userConversationCoachGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Gesprekcoach',
            'uuid' => DummySeeder::DEMO_TWO_CONVERSATION_COACH_UUID,
            'external_id' => DummySeeder::DEMO_TWO_CONVERSATION_COACH_UUID,
            'roles' => 'conversation_coach',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $organisationGgd2->users()->saveMany([
            $userMedicalSupervisorGgd2,
            $userConversationCoachGgd2,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = EloquentUser::find(DummySeeder::DEMO_TWO_CONVERSATION_COACH_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'conversation_coach') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_TWO_MEDICAL_SUPERVISOR_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'medical_supervisor') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_CONVERSATION_COACH_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'conversation_coach') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_MEDICAL_SUPERVISOR_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'medical_supervisor') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'conversation_coach_nationwide') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'medical_supervisor_nationwide') {
            $user->delete();
        }
    }
}
