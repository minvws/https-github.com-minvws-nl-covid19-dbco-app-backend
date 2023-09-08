<?php

declare(strict_types=1);

namespace Tests\Feature\Services\CaseAssignmentService;

use App\Models\Assignment\CaseListAssignment;
use App\Models\Assignment\NullUserAssignment;
use App\Models\Assignment\OrganisationAssignment;
use App\Models\Assignment\UserAssignment;
use App\Models\Eloquent\EloquentOrganisation as Organisation;
use App\Services\CaseAssignmentService;
use Carbon\CarbonImmutable;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('case-assignment')]
#[Group('case-assignment-history')]
class CaseAssignmentServiceAssignmentHistoryTest extends FeatureTestCase
{
    private CaseAssignmentService $caseAssignmentService;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseAssignmentService = app(CaseAssignmentService::class);

        $planner = $this->createUser([], 'planner');
        $this->organisation = $planner->organisations->first();
        $this->be($planner);
    }

    public function testUserAssignmentShouldBeStoredInHistoryTable(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, ['bco_status' => BCOStatus::open()]);

        $user = $this->createUserWithoutOrganisation([
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], 'user');
        $user->organisations()->attach($this->organisation->uuid);

        $this->caseAssignmentService->assignCase($case, new UserAssignment($user));

        $this->assertDatabaseHas('case_assignment_history', [
            'covidcase_uuid' => $case->uuid,
            'assigned_user_uuid' => $user->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);
    }

    public function testOrganisationAssignmentShouldBeStoredInHistoryTable(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, ['bco_status' => BCOStatus::open()]);

        $outsourceOrganisation = $this->createOrganisation([
            'has_outsource_toggle' => 1,
            'is_available_for_outsourcing' => 1,
        ]);

        $this->organisation->outsourceOrganisations()->attach($outsourceOrganisation);
        $this->organisation->save();

        $this->caseAssignmentService->assignCase($case, new OrganisationAssignment($outsourceOrganisation));

        $this->assertDatabaseHas('case_assignment_history', [
            'covidcase_uuid' => $case->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
            'assigned_case_list_uuid' => null,
        ]);
    }

    public function testCaseListAssignmentShouldBeStoredInHistoryTable(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, ['bco_status' => BCOStatus::open()]);
        $caseList = $this->createCaseListForOrganisation($this->organisation);

        $this->caseAssignmentService->assignCase($case, new CaseListAssignment($caseList));

        $this->assertDatabaseHas('case_assignment_history', [
            'covidcase_uuid' => $case->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $caseList->uuid,
            'assigned_case_list_name' => $caseList->name,
        ]);
    }

    public function testUnassignCaseAssignmentShouldBeStoredInHistoryTable(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, ['bco_status' => BCOStatus::open()]);

        $this->caseAssignmentService->assignCase($case, new NullUserAssignment());

        $this->assertDatabaseHas('case_assignment_history', [
            'covidcase_uuid' => $case->uuid,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);
    }

    public function testNewCaseShouldNotContainRecordInHistoryTable(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $this->assertDatabaseMissing('case_assignment_history', [
            'covidcase_uuid' => $case->uuid,
        ]);
    }
}
