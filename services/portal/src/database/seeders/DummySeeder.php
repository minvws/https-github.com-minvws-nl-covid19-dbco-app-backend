<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\OrganisationType;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use MinVWS\DBCO\Enum\Models\Permission;

class DummySeeder extends Seeder
{
    public const DEMO_ORGANISATION_UUID = '00000000-0000-0000-0000-000000000000';
    public const DEMO_OUTSOURCE_ORGANISATION_UUID = '10000000-0000-0000-0000-000000000000';
    public const DEMO_ORGANISATION_TWO_UUID = '20000000-0000-0000-0000-000000000000';
    public const DEMO_OUTSOURCE_ORGANISATION_TWO_UUID = '30000000-0000-0000-0000-000000000000';

    public const DEMO_USER_UUID = '00000000-0000-0000-0000-000000000001';
    public const DEMO_USER_PLANNER_UUID = '00000000-0000-0000-0000-000000000002';
    public const DEMO_PLANNER_UUID = '00000000-0000-0000-0000-000000000004';
    public const DEMO_COMPLIANCE_UUID = '00000000-0000-0000-0000-000000000005';
    public const DEMO_NOROLE_UUID = '00000000-0000-0000-0000-000000000006';
    public const DEMO_CASEQUALITY_UUID = '00000000-0000-0000-0000-000000000007';
    public const DEMO_CASEQUALITY_PLANNER_UUID = '00000000-0000-0000-0000-000000000008';
    public const DEMO_USER_CASEQUALITY_UUID = '00000000-0000-0000-0000-000000000009';
    public const DEMO_USER_CASEQUALITY_PLANNER_UUID = '00000000-0000-0000-0000-000000000010';
    public const DEMO_MEDICAL_SUPERVISOR_UUID = '00000000-0000-0000-0000-000000000011';
    public const DEMO_CONVERSATION_COACH_UUID = '00000000-0000-0000-0000-000000000012';
    public const DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID = '00000000-0000-0000-0000-000000000013';
    public const DEMO_CALLCENTER_UUID = '00000000-0000-0000-0000-000000000014';
    public const DEMO_USER_CALLCENTER_UUID = '00000000-0000-0000-0000-000000000018';
    public const DEMO_USER_MEDICAL_SUPERVISOR_UUID = '00000000-0000-0000-0000-000000000015';
    public const DEMO_DATACATALOG = '00000000-0000-0000-0000-000000000016';
    public const DEMO_CALLCENTER_EXPERT_UUID = '00000000-0000-0000-0000-000000000017';
    public const DEMO_USER_CALLCENTER_EXPERT_UUID = '00000000-0000-0000-0000-000000000019';
    public const DEMO_ADMIN_UUID = '00000000-0000-0000-0000-000000000020';

    public const DEMO_CONTEXTMANAGER_UUID = '00000000-0000-0000-0000-100000000001';
    public const DEMO_USER_CONTEXTMANAGER_UUID = '00000000-0000-0000-0000-100000000002';
    public const DEMO_PLANNER_CONTEXTMANAGER_UUID = '00000000-0000-0000-0000-100000000003';
    public const DEMO_USER_PLANNER_CONTEXTMANAGER_UUID = '00000000-0000-0000-0000-100000000004';
    public const DEMO_USER_PLANNER_CONTEXTMANAGER_CASEQUALITY_UUID = '00000000-0000-0000-0000-100000000005';

    public const DEMO_CLUSTERSPECIALIST_UUID = '00000000-0000-0000-0000-200000000001';
    public const DEMO_USER_CLUSTERSPECIALIST_UUID = '00000000-0000-0000-0000-200000000002';

    public const DEMO_OUTSOURCE_USER_UUID = '10000000-0000-0000-0000-000000000001';
    public const DEMO_OUTSOURCE_PLANNER_UUID = '10000000-0000-0000-0000-000000000002';
    public const DEMO_OUTSOURCE_CASEQUALITY_UUID = '10000000-0000-0000-0000-000000000003';
    public const DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID = '10000000-0000-0000-0000-000000000004';
    public const DEMO_CONVERSATION_COACH_NATIONWIDE_UUID = '10000000-0000-0000-0000-000000000005';

    public const DEMO_TWO_USER_UUID = '20000000-0000-0000-0000-000000000001';
    public const DEMO_TWO_PLANNER_UUID = '20000000-0000-0000-0000-000000000002';
    public const DEMO_TWO_CONTEXTMANAGER_UUID = '20000000-0000-0000-0000-000000000003';
    public const DEMO_TWO_CASEQUALITY_UUID = '20000000-0000-0000-0000-000000000004';
    public const DEMO_TWO_MEDICAL_SUPERVISOR_UUID = '20000000-0000-0000-0000-000000000005';
    public const DEMO_TWO_CONVERSATION_COACH_UUID = '20000000-0000-0000-0000-000000000006';
    public const DEMO_TWO_CALLCENTER_UUID = '20000000-0000-0000-0000-000000000007';
    public const DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID = '20000000-0000-0000-0000-000000000008';
    public const DEMO_TWO_CLUSTERSPECIALIST_UUID = '20000000-0000-0000-0000-000000000009';
    public const DEMO_TWO_CALLCENTER_EXPERT_UUID = '20000000-0000-0000-0000-000000000010';

    public const DEMO_OUTSOURCE_USER_TWO_UUID = '30000000-0000-0000-0000-000000000001';
    public const DEMO_OUTSOURCE_PLANNER_TWO_UUID = '30000000-0000-0000-0000-000000000002';
    public const DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID = '30000000-0000-0000-0000-000000000003';
    public const DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID = '30000000-0000-0000-0000-000000000004';
    public const DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID = '30000000-0000-0000-0000-000000000005';

    public function run(): void
    {
        // delete GHOR organisation because we link GHOR users to the demo organisation
        EloquentOrganisation::query()
            ->where('external_id', OrganisationSeeder::GHOR)
            ->where('type', OrganisationType::regionalGGD())
            ->delete();

        // create organisations
        /** @var EloquentOrganisation $organisationGgd1 */
        $organisationGgd1 = EloquentOrganisation::factory()->create([
            'name' => 'Demo GGD1',
            'type' => OrganisationType::regionalGGD(),
            'abbreviation' => 'GGD1',
            'uuid' => self::DEMO_ORGANISATION_UUID,
            'external_id' => OrganisationSeeder::GHOR, //ggdghor test accounts connect to the Demo organisation using this external_id
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'has_outsource_toggle' => 1,
            'hp_zone_code' => '000000',
            'is_allowed_to_report_test_results' => 1,
        ]);

        /** @var EloquentOrganisation $organisationGgd2 */
        $organisationGgd2 = EloquentOrganisation::factory()->create([
            'name' => 'Demo GGD2',
            'type' => OrganisationType::regionalGGD(),
            'abbreviation' => 'GGD2',
            'uuid' => self::DEMO_ORGANISATION_TWO_UUID,
            'external_id' => 'demo-ggd2',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'has_outsource_toggle' => 1,
            'hp_zone_code' => null,
        ]);

        /** @var EloquentOrganisation $organisationLs1 */
        $organisationLs1 = EloquentOrganisation::factory()->create([
            'name' => 'Demo LS1',
            'type' => OrganisationType::outsourceOrganisation(),
            'abbreviation' => 'LS1',
            'uuid' => self::DEMO_OUTSOURCE_ORGANISATION_UUID,
            'external_id' => 'demo-ls1',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'has_outsource_toggle' => 0,
            'is_available_for_outsourcing' => 1,
            'hp_zone_code' => null,
        ]);

        /** @var EloquentOrganisation $organisationLs2 */
        $organisationLs2 = EloquentOrganisation::factory()->create([
            'name' => 'Demo LS2',
            'type' => OrganisationType::outsourceOrganisation(),
            'abbreviation' => 'LS2',
            'uuid' => self::DEMO_OUTSOURCE_ORGANISATION_TWO_UUID,
            'external_id' => 'demo-ls2',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'has_outsource_toggle' => 0,
            'is_available_for_outsourcing' => 1,
            'hp_zone_code' => null,
        ]);

        // attach outsource organisations
        $organisationGgd1->outsourceOrganisations()->save($organisationLs1);
        $organisationGgd2->outsourceOrganisations()->save($organisationLs1);
        $organisationGgd1->outsourceOrganisations()->save($organisationLs2);
        $organisationGgd2->outsourceOrganisations()->save($organisationLs2);

        // attach labels to organisations
        $caseLabels = CaseLabel::all();
        $organisationGgd1->caseLabels()->sync($caseLabels);
        $organisationGgd2->caseLabels()->sync($caseLabels);
        $organisationLs1->caseLabels()->sync($caseLabels);
        $organisationLs2->caseLabels()->sync($caseLabels);

        // create caseLists
        CaseList::factory()->create([
            'uuid' => (string) Str::uuid(),
            'organisation_uuid' => self::DEMO_ORGANISATION_UUID,
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
        ]);

        CaseList::factory()->create([
            'uuid' => (string) Str::uuid(),
            'organisation_uuid' => self::DEMO_OUTSOURCE_ORGANISATION_UUID,
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
        ]);

        CaseList::factory()->create([
            'uuid' => (string) Str::uuid(),
            'organisation_uuid' => self::DEMO_ORGANISATION_TWO_UUID,
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
        ]);

        CaseList::factory()->create([
            'uuid' => (string) Str::uuid(),
            'organisation_uuid' => self::DEMO_OUTSOURCE_ORGANISATION_TWO_UUID,
            'name' => 'Wachtrij',
            'is_default' => 1,
            'is_queue' => 1,
        ]);

        // create users
        EloquentUser::factory()->create([
            'name' => 'GGD GHOR: Beheerder',
            'uuid' => self::DEMO_ADMIN_UUID,
            'external_id' => self::DEMO_ADMIN_UUID,
            'roles' => 'admin',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
            'last_login_at' => CarbonImmutable::now(),
        ]);

        $userUserGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker',
            'uuid' => self::DEMO_USER_UUID,
            'external_id' => self::DEMO_USER_UUID,
            'roles' => 'user',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
            'last_login_at' => CarbonImmutable::now(),
        ]);

        //Not saving this user to a variable because it is not part of a GGD organisation.
        EloquentUser::factory()->create([
            'name' => 'Demo DataCatalog',
            'uuid' => self::DEMO_DATACATALOG,
            'external_id' => self::DEMO_DATACATALOG,
            'roles' => Permission::datacatalog()->value,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
            'last_login_at' => CarbonImmutable::now(),
        ]);

        $userUserPlannerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Werkverdeler',
            'uuid' => self::DEMO_USER_PLANNER_UUID,
            'external_id' => self::DEMO_USER_PLANNER_UUID,
            'roles' => 'user,planner',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
            'last_login_at' => CarbonImmutable::now(),
        ]);

        $userPlannerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Werkverdeler',
            'uuid' => self::DEMO_PLANNER_UUID,
            'external_id' => self::DEMO_PLANNER_UUID,
            'roles' => 'planner',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
            'last_login_at' => CarbonImmutable::now(),
        ]);

        $userComplianceGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Compliance Officer',
            'uuid' => self::DEMO_COMPLIANCE_UUID,
            'external_id' => self::DEMO_COMPLIANCE_UUID,
            'roles' => 'compliance',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userNoRoleGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Geen Rol',
            'uuid' => self::DEMO_NOROLE_UUID,
            'external_id' => self::DEMO_NOROLE_UUID,
            'roles' => null,
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Gebruiker',
            'uuid' => self::DEMO_TWO_USER_UUID,
            'external_id' => self::DEMO_TWO_USER_UUID,
            'roles' => 'user',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userPlannerGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Werkverdeler',
            'uuid' => self::DEMO_TWO_PLANNER_UUID,
            'external_id' => self::DEMO_TWO_PLANNER_UUID,
            'roles' => 'planner',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCasequaltityGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Dossierkwaliteit',
            'uuid' => self::DEMO_TWO_CASEQUALITY_UUID,
            'external_id' => self::DEMO_TWO_CASEQUALITY_UUID,
            'roles' => 'casequality',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userMedicalSupervisorGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Medische Supervisor',
            'uuid' => self::DEMO_MEDICAL_SUPERVISOR_UUID,
            'external_id' => self::DEMO_MEDICAL_SUPERVISOR_UUID,
            'roles' => 'medical_supervisor',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserMedicalSupervisorGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Medische Supervisor',
            'uuid' => self::DEMO_USER_MEDICAL_SUPERVISOR_UUID,
            'external_id' => self::DEMO_USER_MEDICAL_SUPERVISOR_UUID,
            'roles' => 'user,medical_supervisor',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userConversationCoachGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gesprekcoach',
            'uuid' => self::DEMO_CONVERSATION_COACH_UUID,
            'external_id' => self::DEMO_CONVERSATION_COACH_UUID,
            'roles' => 'conversation_coach',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userMedicalSupervisorConversationCoachGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Medische supervisor & Gesprekcoach',
            'uuid' => self::DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID,
            'external_id' => self::DEMO_MEDICAL_SUPERVISOR_CONVERSATION_COACH_UUID,
            'roles' => 'medical_supervisor,conversation_coach',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCallcenterGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Callcenter Basis',
            'uuid' => self::DEMO_CALLCENTER_UUID,
            'external_id' => self::DEMO_CALLCENTER_UUID,
            'roles' => 'callcenter',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserCallcenterGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Callcenter Basis',
            'uuid' => self::DEMO_USER_CALLCENTER_UUID,
            'external_id' => self::DEMO_USER_CALLCENTER_UUID,
            'roles' => 'user,callcenter',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCallcenterExpertGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Callcenter Expert',
            'uuid' => self::DEMO_CALLCENTER_EXPERT_UUID,
            'external_id' => self::DEMO_CALLCENTER_EXPERT_UUID,
            'roles' => 'callcenter_expert',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserCallcenterExpertGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Callcenter Expert',
            'uuid' => self::DEMO_USER_CALLCENTER_EXPERT_UUID,
            'external_id' => self::DEMO_USER_CALLCENTER_EXPERT_UUID,
            'roles' => 'user,callcenter_expert',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userMedicalSupervisorLs1 = EloquentUser::factory()->create([
            'name' => 'Demo LS1 Medische Supervisor',
            'uuid' => self::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID,
            'external_id' => self::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_UUID,
            'roles' => 'medical_supervisor_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userConversationCoachLs1 = EloquentUser::factory()->create([
            'name' => 'Demo LS1 Gesprekcoach',
            'uuid' => self::DEMO_CONVERSATION_COACH_NATIONWIDE_UUID,
            'external_id' => self::DEMO_CONVERSATION_COACH_NATIONWIDE_UUID,
            'roles' => 'conversation_coach_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userMedicalSupervisorGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Medische Supervisor',
            'uuid' => self::DEMO_TWO_MEDICAL_SUPERVISOR_UUID,
            'external_id' => self::DEMO_TWO_MEDICAL_SUPERVISOR_UUID,
            'roles' => 'medical_supervisor',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserMedicalSupervisorGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Gebruiker & Medische Supervisor',
            'uuid' => self::DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID,
            'external_id' => self::DEMO_TWO_USER_MEDICAL_SUPERVISOR_UUID,
            'roles' => 'user,medical_supervisor',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userConversationCoachGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Gesprekcoach',
            'uuid' => self::DEMO_TWO_CONVERSATION_COACH_UUID,
            'external_id' => self::DEMO_TWO_CONVERSATION_COACH_UUID,
            'roles' => 'conversation_coach',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCallcenterGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Callcenter Basis',
            'uuid' => self::DEMO_TWO_CALLCENTER_UUID,
            'external_id' => self::DEMO_TWO_CALLCENTER_UUID,
            'roles' => 'callcenter',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCallcenterExpertGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Callcenter Expert',
            'uuid' => self::DEMO_TWO_CALLCENTER_EXPERT_UUID,
            'external_id' => self::DEMO_TWO_CALLCENTER_EXPERT_UUID,
            'roles' => 'callcenter_expert',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userOutsourceLs1 = EloquentUser::factory()->create([
            'name' => 'Demo LS1 Gebruiker',
            'uuid' => self::DEMO_OUTSOURCE_USER_UUID,
            'external_id' => self::DEMO_OUTSOURCE_USER_UUID,
            'roles' => 'user_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userPlannerLs1 = EloquentUser::factory()->create([
            'name' => 'Demo LS1 Werkverdeler',
            'uuid' => self::DEMO_OUTSOURCE_PLANNER_UUID,
            'external_id' => self::DEMO_OUTSOURCE_PLANNER_UUID,
            'roles' => 'planner_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCasequalityLs1 = EloquentUser::factory()->create([
            'name' => 'Demo LS1 Dossierkwaliteit',
            'uuid' => self::DEMO_OUTSOURCE_CASEQUALITY_UUID,
            'external_id' => self::DEMO_OUTSOURCE_CASEQUALITY_UUID,
            'roles' => 'casequality_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userContextmanagerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Contextbeheerder',
            'uuid' => self::DEMO_CONTEXTMANAGER_UUID,
            'external_id' => self::DEMO_CONTEXTMANAGER_UUID,
            'roles' => 'contextmanager',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userContextmanagerGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Contextbeheerder',
            'uuid' => self::DEMO_TWO_CONTEXTMANAGER_UUID,
            'external_id' => self::DEMO_TWO_CONTEXTMANAGER_UUID,
            'roles' => 'contextmanager',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserContextmanagerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Contextbeheerder',
            'uuid' => self::DEMO_USER_CONTEXTMANAGER_UUID,
            'external_id' => self::DEMO_USER_CONTEXTMANAGER_UUID,
            'roles' => 'user,contextmanager',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);


        $userClusterspecialistGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Clusterspecialist',
            'uuid' => self::DEMO_CLUSTERSPECIALIST_UUID,
            'external_id' => self::DEMO_CLUSTERSPECIALIST_UUID,
            'roles' => 'clusterspecialist',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userClusterspecialistGgd2 = EloquentUser::factory()->create([
            'name' => 'Demo GGD2 Clusterspecialist',
            'uuid' => self::DEMO_TWO_CLUSTERSPECIALIST_UUID,
            'external_id' => self::DEMO_TWO_CLUSTERSPECIALIST_UUID,
            'roles' => 'clusterspecialist',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserClusterspecialistGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Clusterspecialist',
            'uuid' => self::DEMO_USER_CLUSTERSPECIALIST_UUID,
            'external_id' => self::DEMO_USER_CLUSTERSPECIALIST_UUID,
            'roles' => 'user,clusterspecialist',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userPlannerContextmanagerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Planner & Contextbeheerder',
            'uuid' => self::DEMO_PLANNER_CONTEXTMANAGER_UUID,
            'external_id' => self::DEMO_PLANNER_CONTEXTMANAGER_UUID,
            'roles' => 'planner,contextmanager',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserPlannerContextmanagerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Planner & Contextbeheerder',
            'uuid' => self::DEMO_USER_PLANNER_CONTEXTMANAGER_UUID,
            'external_id' => self::DEMO_USER_PLANNER_CONTEXTMANAGER_UUID,
            'roles' => 'user,planner,contextmanager',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserPlannerContextmanagerCasequalityGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Planner & Contextbeheerder & Dossierkwaliteit',
            'uuid' => self::DEMO_USER_PLANNER_CONTEXTMANAGER_CASEQUALITY_UUID,
            'external_id' => self::DEMO_USER_PLANNER_CONTEXTMANAGER_CASEQUALITY_UUID,
            'roles' => 'user,planner,contextmanager,casequality',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCasequaltityGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Dossierkwaliteit',
            'uuid' => self::DEMO_CASEQUALITY_UUID,
            'external_id' => self::DEMO_CASEQUALITY_UUID,
            'roles' => 'casequality',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCasequaltityPlannerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Dossierkwaliteit & Planner',
            'uuid' => self::DEMO_CASEQUALITY_PLANNER_UUID,
            'external_id' => self::DEMO_CASEQUALITY_PLANNER_UUID,
            'roles' => 'casequality,planner',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserCasequaltityGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Dossierkwaliteit',
            'uuid' => self::DEMO_USER_CASEQUALITY_UUID,
            'external_id' => self::DEMO_USER_CASEQUALITY_UUID,
            'roles' => 'user,casequality',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userUserCasequaltityPlannerGgd1 = EloquentUser::factory()->create([
            'name' => 'Demo GGD1 Gebruiker & Dossierkwaliteit & Werkverdeler',
            'uuid' => self::DEMO_USER_CASEQUALITY_PLANNER_UUID,
            'external_id' => self::DEMO_USER_CASEQUALITY_PLANNER_UUID,
            'roles' => 'user,casequality,planner',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userMedicalSupervisorLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Medische Supervisor',
            'uuid' => self::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID,
            'external_id' => self::DEMO_MEDICAL_SUPERVISOR_NATIONWIDE_TWO_UUID,
            'roles' => 'medical_supervisor_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userConversationCoachLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Gesprekcoach',
            'uuid' => self::DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID,
            'external_id' => self::DEMO_CONVERSATION_COACH_NATIONWIDE_TWO_UUID,
            'roles' => 'conversation_coach_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userOutsourceLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Gebruiker',
            'uuid' => self::DEMO_OUTSOURCE_USER_TWO_UUID,
            'external_id' => self::DEMO_OUTSOURCE_USER_TWO_UUID,
            'roles' => 'user_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userPlannerLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Werkverdeler',
            'uuid' => self::DEMO_OUTSOURCE_PLANNER_TWO_UUID,
            'external_id' => self::DEMO_OUTSOURCE_PLANNER_TWO_UUID,
            'roles' => 'planner_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        $userCasequalityLs2 = EloquentUser::factory()->create([
            'name' => 'Demo LS2 Dossierkwaliteit',
            'uuid' => self::DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID,
            'external_id' => self::DEMO_OUTSOURCE_CASEQUALITY_TWO_UUID,
            'roles' => 'casequality_nationwide',
            'created_at' => CarbonImmutable::now(),
            'updated_at' => CarbonImmutable::now(),
            'consented_at' => CarbonImmutable::now(),
        ]);

        // attach users to organisations
        $organisationGgd1->users()->saveMany([
            $userUserGgd1,
            $userUserPlannerGgd1,
            $userPlannerGgd1,
            $userComplianceGgd1,
            $userNoRoleGgd1,
            $userContextmanagerGgd1,
            $userUserContextmanagerGgd1,
            $userClusterspecialistGgd1,
            $userUserClusterspecialistGgd1,
            $userPlannerContextmanagerGgd1,
            $userUserPlannerContextmanagerGgd1,
            $userUserPlannerContextmanagerCasequalityGgd1,
            $userCasequaltityGgd1,
            $userCasequaltityPlannerGgd1,
            $userUserCasequaltityGgd1,
            $userUserCasequaltityPlannerGgd1,
            $userMedicalSupervisorGgd1,
            $userUserMedicalSupervisorGgd1,
            $userConversationCoachGgd1,
            $userMedicalSupervisorConversationCoachGgd1,
            $userCallcenterGgd1,
            $userUserCallcenterGgd1,
            $userCallcenterExpertGgd1,
            $userUserCallcenterExpertGgd1,
        ]);

        $organisationGgd2->users()->saveMany([
            $userUserGgd2,
            $userPlannerGgd2,
            $userContextmanagerGgd2,
            $userClusterspecialistGgd2,
            $userCasequaltityGgd2,
            $userMedicalSupervisorGgd2,
            $userUserMedicalSupervisorGgd2,
            $userConversationCoachGgd2,
            $userCallcenterGgd2,
            $userCallcenterExpertGgd2,
        ]);

        $organisationLs1->users()->saveMany([
            $userOutsourceLs1,
            $userPlannerLs1,
            $userCasequalityLs1,
            $userMedicalSupervisorLs1,
            $userConversationCoachLs1,
        ]);

        $organisationLs2->users()->saveMany([
            $userOutsourceLs2,
            $userPlannerLs2,
            $userCasequalityLs2,
            $userMedicalSupervisorLs2,
            $userConversationCoachLs2,
        ]);
    }
}
