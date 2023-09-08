<?php

declare(strict_types=1);

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\OrganisationType;
use Carbon\CarbonImmutable;
use Database\Seeders\DummySeeder;
use Illuminate\Database\Migrations\Migration;

class SeedNewUserOrganisationAndRoles extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $organisationLs1 = EloquentOrganisation::where('uuid', DummySeeder::DEMO_OUTSOURCE_ORGANISATION_UUID)->first();

        // Create GGD nationwide organisation 2 and users.
        if ($organisationLs1 === null) {
            return;
        }

        /** @var EloquentOrganisation $organisationLs2 */
        $organisationLs2 = EloquentOrganisation::factory()->create([
            'name' => 'Demo LS2',
            'type' => OrganisationType::outsourceOrganisation(),
            'abbreviation' => 'LS2',
            'uuid' => DummySeeder::DEMO_OUTSOURCE_ORGANISATION_TWO_UUID,
            'external_id' => 'demo-ls2',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'has_outsource_toggle' => 0,
            'hp_zone_code' => null,
        ]);

        if ($organisationLs2 === null) {
            return;
        }

        $userMedicalSupervisorLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Medische Supervisor',
            'uuid' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID,
            'external_id' => DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID,
            'roles' => 'medical_supervisor_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userConversationCoachLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Gesprekcoach',
            'uuid' => DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID,
            'external_id' => DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID,
            'roles' => 'conversation_coach_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userOutsourceLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Gebruiker',
            'uuid' => DummySeeder::DEMO_OUTSOURCE_USER_TWO_UUID,
            'external_id' => DummySeeder::DEMO_OUTSOURCE_USER_TWO_UUID,
            'roles' => 'user_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userPlannerLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Werkverdeler',
            'uuid' => DummySeeder::DEMO_OUTSOURCE_PLANNER_TWO_UUID,
            'external_id' => DummySeeder::DEMO_OUTSOURCE_PLANNER_TWO_UUID,
            'roles' => 'planner_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCasequalityLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Dossierkwaliteit',
            'uuid' => DummySeeder::DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID,
            'external_id' => DummySeeder::DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID,
            'roles' => 'casequality_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        // Save user to organisation.
        $organisationLs2->users()->saveMany([
            $userMedicalSupervisorLs2,
            $userConversationCoachLs2,
            $userOutsourceLs2,
            $userPlannerLs2,
            $userCasequalityLs2,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $user = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'casequality_nationwide') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_PLANNER_TWO_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'planner_nationwide') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_OUTSOURCE_USER_TWO_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'user_nationwide') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'conversation_coach_nationwide') {
            $user->delete();
        }

        $user = EloquentUser::find(DummySeeder::DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID);
        if ($user instanceof EloquentUser && $user->roles === 'medical_supervisor_nationwide') {
            $user->delete();
        }
    }
}
