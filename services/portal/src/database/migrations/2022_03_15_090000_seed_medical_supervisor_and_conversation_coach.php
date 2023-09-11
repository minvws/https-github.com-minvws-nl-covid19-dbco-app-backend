<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class SeedMedicalSupervisorAndConversationCoach extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Get GGD organisation 1.
        $organisationGgd1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_ORGANISATION_UUID)->first();

        if ($organisationGgd1 === null) {
            return;
        }

        $userMedicalSupervisorConversationCoachGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Medische Supervisor & Gesprekscoach',
            'uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID,
            'external_id' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID,
            'roles' => 'medical_supervisor,conversation_coach',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        // Save users to organisation.
        $organisationGgd1->users()->saveMany([
            $userMedicalSupervisorConversationCoachGgd1,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = EloquentUser::find(DummySeeder::DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'medical_supervisor,conversation_coach') {
            $user->delete();
        }
    }
}
