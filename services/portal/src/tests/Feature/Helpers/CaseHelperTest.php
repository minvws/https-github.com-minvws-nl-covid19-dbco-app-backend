<?php

declare(strict_types=1);

namespace Tests\Feature\Helpers;

use App\Helpers\CaseHelper;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\PlannerCase\PlannerView;
use App\Services\AuthenticationService;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('casehelper')]
class CaseHelperTest extends FeatureTestCase
{
    private AuthenticationService $authService;

    private EloquentUser $userA;
    private EloquentUser $userB;

    private EloquentUser $plannerA;
    private EloquentUser $plannerB;

    private EloquentOrganisation $organisationA;
    private EloquentOrganisation $organisationB;

    private CaseList $caseListA;
    private CaseList $caseListB;

    private CaseList $queueCaseListA;
    private CaseList $queueCaseListB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authService = app(AuthenticationService::class);

        $this->organisationA = $this->createOrganisation();
        $this->organisationB = $this->createOrganisation();

        $this->userA = $this->createUserForOrganisation($this->organisationA);
        $this->userB = $this->createUserForOrganisation($this->organisationB);

        $this->plannerA = $this->createUserForOrganisation($this->organisationA, [], 'planner');
        $this->plannerB = $this->createUserForOrganisation($this->organisationB, [], 'planner');

        $this->caseListA = $this->createCaseListForOrganisation($this->organisationA);
        $this->caseListB = $this->createCaseListForOrganisation($this->organisationB);

        $this->queueCaseListA = $this->createCaseListForOrganisation($this->organisationA, ['is_queue' => true]);
        $this->queueCaseListB = $this->createCaseListForOrganisation($this->organisationB, ['is_queue' => true]);
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsNotAssigned(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::open(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::unassigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToUserA(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => $this->userA->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToCaselistAButNotToAUser(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $this->caseListA,
        ]);

        $this->assertEqualEnum(PlannerView::unassigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToCaselistAAndToUserA(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => $this->userA,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $this->caseListA,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToAQueueButNotToAUser(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $this->queueCaseListA,
        ]);

        $this->assertEqualEnum(PlannerView::queued(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBButNotAssignedToAUser(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::outsourced(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndToUserB(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => $this->userB,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::outsourced(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndCaseListBButNotToAUser(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => $this->caseListB,
        ]);

        $this->assertEqualEnum(PlannerView::outsourced(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndToCaseListBAndToUserB(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => $this->userB,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => $this->caseListB,
        ]);

        $this->assertEqualEnum(PlannerView::outsourced(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerBIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBButNotAssignedToAUser(): void
    {
        $this->be($this->plannerB);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::draft(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::unassigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerBIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndAssignedToUserB(): void
    {
        $this->be($this->plannerB);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::open(),
            'assigned_user_uuid' => $this->userB,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerBIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndCaseListBAndNotToAUser(): void
    {
        $this->be($this->plannerB);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::open(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => $this->caseListB,
        ]);

        $this->assertEqualEnum(PlannerView::unassigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerBIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndToCaseListBAndToUserB(): void
    {
        $this->be($this->plannerB);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::open(),
            'assigned_user_uuid' => $this->userB,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => $this->caseListB,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerBIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndToQueueCaseListBAndNotToAUser(): void
    {
        $this->be($this->plannerB);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::open(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => $this->queueCaseListB,
        ]);

        $this->assertEqualEnum(PlannerView::queued(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerBIRetrieveACaseFromOrganisationAWhichIsAssignedToOrganisationBAndToQueueCaseListBAndToUserB(): void
    {
        $this->be($this->plannerB);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::open(),
            'assigned_user_uuid' => $this->userB,
            'assigned_organisation_uuid' => $this->organisationB,
            'assigned_case_list_uuid' => $this->queueCaseListB,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsCompleted(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::completed(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::completed(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsCompletedButDeclinedByCasequality(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::completed(),
            'is_approved' => false,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::unassigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsCompletedButDeclinedByCasequalityButAssignedToCaselistAButNotToAUser(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::completed(),
            'is_approved' => false,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => $this->caseListA->uuid,
        ]);

        $this->assertEqualEnum(PlannerView::unassigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsCompletedButDeclinedByCasequalityButAssignedToUserA(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::completed(),
            'is_approved' => false,
            'assigned_user_uuid' => $this->userA->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsCompletedButDeclinedByCasequalityButAssignedToCaselistAAndUserA(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::completed(),
            'is_approved' => false,
            'assigned_user_uuid' => $this->userA->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::assigned(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsArchived(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::archived(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::archived(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsArchivedButDeclinedByCasequality(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::archived(),
            'is_approved' => false,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::archived(), $this->getCaseHelperPlannerView($case));
    }

    public function testAsPlannerAIRetrieveACaseFromOrganisationAWhichIsArchivedAndApprovedByCasequality(): void
    {
        $this->be($this->plannerA);
        $case = $this->createCaseForOrganisation($this->organisationA, [
            'bco_status' => BCOStatus::archived(),
            'is_approved' => true,
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);

        $this->assertEqualEnum(PlannerView::archived(), $this->getCaseHelperPlannerView($case));
    }

    private function assertEqualEnum(PlannerView $expected, PlannerView $actual): void
    {
        $this->assertEquals($expected->value, $actual->value);
    }

    private function getCaseHelperPlannerView(EloquentCase $case): PlannerView
    {
        return CaseHelper::getPlannerView($case, $this->authService->getRequiredSelectedOrganisation()->uuid);
    }
}
