<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\AuthorizationService;
use MinVWS\DBCO\Enum\Models\Permission;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\TestDox;
use Tests\TestCase;

use function app;
use function collect;
use function implode;

use const PHP_EOL;

#[Group('authorization')]
class AuthorizationServiceTest extends TestCase
{
    private AuthorizationService $authorizationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizationService = app(AuthorizationService::class);
    }

    #[DataProvider('userRoleDataProvider')]
    #[DataProvider('userRoleNationwideDataProvider')]
    #[DataProvider('plannerRoleDataProvider')]
    #[DataProvider('userPlannerRoleDataProvider')]
    #[DataProvider('complianceRoleDataProvider')]
    #[DataProvider('casequalityRoleDataProvider')]
    #[TestDox("Test that role has permission to '\$permission' asserts \$expectedResult")]
    public function testRole(array $roles, Permission $permission, bool $expectedResult): void
    {
        $this->assertEquals(
            $expectedResult,
            $this->authorizationService->hasPermission($roles, $permission->value),
            'Given the roles "' . implode(
                ',',
                $roles,
            ) . '", when checking the permission, "' . $permission->value . '", the expected outcome is: "' . $expectedResult . '". However, it is not.',
        );
    }

    public static function userRoleDataProvider(): array
    {
        $roles = ['user'];
        return [
            [$roles, Permission::caseUserEdit(), true],
            [$roles, Permission::casePlannerEdit(), false],
            [$roles, Permission::caseCreate(), true],
            [$roles, Permission::caseListUserCases(), true],
            [$roles, Permission::caseListPlannerCases(), false],
            [$roles, Permission::caseCreateCallToAction(), true],
            [$roles, Permission::caseViewCallToAction(), true],
            [$roles, Permission::casePlannerDelete(), false],
            [$roles, Permission::caseCanPickUpNew(), true],
            [$roles, Permission::caseBcoPhaseEdit(), true],
            [$roles, Permission::taskEdit(), true],
            [$roles, Permission::caseListAccessRequests(), false],
            [$roles, Permission::caseViewAccessRequest(), false],
            [$roles, Permission::caseRestore(), false],
            [$roles, Permission::taskRestore(), false],
            [$roles, Permission::caseCanBeAssignedToDraft(), true],
            [$roles, Permission::caseCanBeAssignedToOpen(), true],
            [$roles, Permission::caseCanBeAssignedToArchived(), true],
            [$roles, Permission::caseCanBeAssignedToUnknown(), true],
            [$roles, Permission::contextLink(), true],
            [$roles, Permission::placeCreate(), true],
            [$roles, Permission::placeEditOwnedByOrganisation(), true],
            [$roles, Permission::placeEditNotOwnedByOrganisation(), true],
            [$roles, Permission::placeSearch(), true],
            [$roles, Permission::placeSectionList(), true],
            [$roles, Permission::placeSectionCreateOwnedByOrganisation(), true],
            [$roles, Permission::placeSectionCreateNotOwnedByOrganisation(), true],
            [$roles, Permission::choreList(), true],
        ];
    }

    public static function userRoleNationwideDataProvider(): array
    {
        $roles = ['user_nationwide'];
        return [
            [$roles, Permission::caseUserEdit(), true],
            [$roles, Permission::casePlannerEdit(), false],
            [$roles, Permission::caseCreate(), false],
            [$roles, Permission::caseListUserCases(), true],
            [$roles, Permission::caseCreateCallToAction(), true],
            [$roles, Permission::caseViewCallToAction(), true],
            [$roles, Permission::caseListPlannerCases(), false],
            [$roles, Permission::casePlannerDelete(), false],
            [$roles, Permission::caseCanPickUpNew(), true],
            [$roles, Permission::caseBcoPhaseEdit(), true],
            [$roles, Permission::taskEdit(), true],
            [$roles, Permission::caseListAccessRequests(), false],
            [$roles, Permission::caseViewAccessRequest(), false],
            [$roles, Permission::caseRestore(), false],
            [$roles, Permission::taskRestore(), false],
            [$roles, Permission::caseCanBeAssignedToDraft(), true],
            [$roles, Permission::caseCanBeAssignedToOpen(), true],
            [$roles, Permission::caseCanBeAssignedToArchived(), true],
            [$roles, Permission::caseCanBeAssignedToUnknown(), true],
            [$roles, Permission::contextLink(), true],
            [$roles, Permission::placeCreate(), true],
            [$roles, Permission::placeEditOwnedByOrganisation(), true],
            [$roles, Permission::placeEditNotOwnedByOrganisation(), true],
            [$roles, Permission::placeSearch(), true],
            [$roles, Permission::placeSectionList(), true],
            [$roles, Permission::placeSectionCreateOwnedByOrganisation(), true],
            [$roles, Permission::placeSectionCreateNotOwnedByOrganisation(), true],
            [$roles, Permission::choreList(), true],
        ];
    }

    public static function plannerRoleDataProvider(): array
    {
        $roles = ['planner'];
        return [
            [$roles, Permission::caseUserEdit(), false],
            [$roles, Permission::caseViewSupervisionRegional(), false],
            [$roles, Permission::casePlannerEdit(), true],
            [$roles, Permission::caseCreate(), true],
            [$roles, Permission::caseListUserCases(), false],
            [$roles, Permission::caseListPlannerCases(), true],
            [$roles, Permission::caseViewCallToAction(), false],
            [$roles, Permission::caseArchive(), true],
            [$roles, Permission::caseArchiveDirectly(), true],
            [$roles, Permission::casePlannerDelete(), true],
            [$roles, Permission::caseCanPickUpNew(), false],
            [$roles, Permission::caseBcoPhaseEdit(), true],
            [$roles, Permission::caseComplianceDelete(), false],
            [$roles, Permission::taskComplianceDelete(), false],
            [$roles, Permission::caseListAccessRequests(), false],
            [$roles, Permission::caseViewAccessRequest(), false],
            [$roles, Permission::caseRestore(), false],
            [$roles, Permission::caseReopen(), true],
            [$roles, Permission::taskRestore(), false],
            [$roles, Permission::organisationList(), true],
            [$roles, Permission::organisationUpdate(), true],
            [$roles, Permission::choreList(), true],
            [$roles, Permission::caseMetricsList(), true],
        ];
    }

    public static function userPlannerRoleDataProvider(): array
    {
        $roles = ['user', 'planner'];
        return [
            [$roles, Permission::caseUserEdit(), true],
            [$roles, Permission::casePlannerEdit(), true],
            [$roles, Permission::caseCreate(), true],
            [$roles, Permission::caseListUserCases(), true],
            [$roles, Permission::caseCreateCallToAction(), true],
            [$roles, Permission::caseViewCallToAction(), true],
            [$roles, Permission::caseListPlannerCases(), true],
            [$roles, Permission::casePlannerDelete(), true],
            [$roles, Permission::caseComplianceDelete(), false],
            [$roles, Permission::caseCanPickUpNew(), true],
            [$roles, Permission::caseBcoPhaseEdit(), true],
            [$roles, Permission::taskEdit(), true],
            [$roles, Permission::caseListAccessRequests(), false],
            [$roles, Permission::caseViewAccessRequest(), false],
            [$roles, Permission::caseCanBeAssignedToDraft(), true],
            [$roles, Permission::caseCanBeAssignedToOpen(), true],
            [$roles, Permission::caseCanBeAssignedToArchived(), true],
            [$roles, Permission::caseCanBeAssignedToUnknown(), true],
            [$roles, Permission::organisationList(), true],
            [$roles, Permission::organisationUpdate(), true],
            [$roles, Permission::choreList(), true],
            [$roles, Permission::caseMetricsList(), true],
        ];
    }

    public static function complianceRoleDataProvider(): array
    {
        $roles = ['compliance'];
        return [
            [$roles, Permission::caseUserEdit(), false],
            [$roles, Permission::caseCreate(), false],
            [$roles, Permission::caseListUserCases(), false],
            [$roles, Permission::caseListPlannerCases(), false],
            [$roles, Permission::caseViewCallToAction(), false],
            [$roles, Permission::casePlannerDelete(), false],
            [$roles, Permission::taskUserDelete(), false],
            [$roles, Permission::taskComplianceDelete(), true],
            [$roles, Permission::caseListAccessRequests(), true],
            [$roles, Permission::caseViewAccessRequest(), true],
            [$roles, Permission::caseRestore(), true],
            [$roles, Permission::taskRestore(), true],
            [$roles, Permission::choreList(), true],
        ];
    }

    public static function casequalityRoleDataProvider(): array
    {
        $roles = ['casequality', 'casequality_nationwide'];
        return [
            [$roles, Permission::caseApprove(), true],
            [$roles, Permission::caseUserEdit(), true],
            [$roles, Permission::caseArchive(), true],
            [$roles, Permission::caseListUserCases(), true],
            [$roles, Permission::caseViewCallToAction(), false],
            [$roles, Permission::caseEditContactStatus(), true],
            [$roles, Permission::caseCanBeAssignedToCompleted(), true],
            [$roles, Permission::caseExport(), true],
            [$roles, Permission::caseBcoPhaseEdit(), true],
            [$roles, Permission::contextCreate(), true],
            [$roles, Permission::contextEdit(), true],
            [$roles, Permission::contextDelete(), true],
            [$roles, Permission::taskCreate(), true],
            [$roles, Permission::taskEdit(), true],
            [$roles, Permission::taskUserDelete(), true],
            [$roles, Permission::caseBsnLookup(), true],
            [$roles, Permission::choreList(), true],
        ];
    }

    #[DataProvider('getPermissionsDataProvider')]
    #[TestDox('AutorizationService builds flat list of permissions from roles array')]
    public function testGetPermissionsForRole(array $roles, array $expectedResult): void
    {
        $result = $this->authorizationService->getPermissionsForRoles($roles);
        $this->assertEqualsCanonicalizing(
            $expectedResult,
            $result,
            'Expected ' . PHP_EOL . implode(PHP_EOL, $expectedResult) . PHP_EOL . PHP_EOL . ' Got ' . PHP_EOL . collect($result)
                ->map(static fn(Permission $permission) => $permission->value)
                ->join(PHP_EOL),
        );
    }

    public static function getPermissionsDataProvider(): array
    {
        $caseEditPermissions = [
            Permission::caseUserEdit(),
            Permission::caseEditContactStatus(),
            Permission::caseBcoPhaseEdit(),
            Permission::contextCreate(),
            Permission::contextEdit(),
            Permission::contextDelete(),
            Permission::contextLink(),
            Permission::taskCreate(),
            Permission::taskEdit(),
            Permission::taskUserDelete(),
            Permission::caseBsnLookup(),
            Permission::caseAddressLookup(),
            Permission::caseViewUserTimeline(),
            Permission::caseViewOsirisHistory(),
            Permission::caseCreateCallToAction(),
            Permission::organisationList(),
            Permission::placeCreate(),
            Permission::placeEdit(),
            Permission::placeEditOwnedByOrganisation(),
            Permission::placeEditNotOwnedByOrganisation(),
            Permission::placeSearch(),
            Permission::placeSectionList(),
            Permission::placeSectionCreate(),
            Permission::placeSectionCreateOwnedByOrganisation(),
            Permission::placeSectionCreateNotOwnedByOrganisation(),
            Permission::choreList(),
            Permission::callToAction(),
        ];

        return [
            [

                ['user','user_nationwide'],
                [
                    ...$caseEditPermissions,
                    Permission::caseCreate(),
                    Permission::caseListUserCases(),
                    Permission::caseExport(),
                    Permission::caseCanPickUpNew(),
                    Permission::caseCanBeAssignedToOpen(),
                    Permission::caseCanBeAssignedToUnknown(),
                    Permission::caseCanBeAssignedToDraft(),
                    Permission::caseCanBeAssignedToArchived(),
                    Permission::caseViewCallToAction(),
                    Permission::datacatalog(),
                ]],
            [

                ['compliance'],
                [
                    Permission::caseListAccessRequests(),
                    Permission::caseViewAccessRequest(),
                    Permission::taskViewAccessRequest(),
                    Permission::caseComplianceDelete(),
                    Permission::taskComplianceDelete(),
                    Permission::caseRestore(),
                    Permission::taskRestore(),
                    Permission::choreList(),
                    Permission::datacatalog(),
                ]],
            [

                ['planner', 'planner_nationwide'],
                [
                    Permission::caseListPlannerCases(),
                    Permission::caseCreate(),
                    Permission::casePlannerEdit(),
                    Permission::casePlannerDelete(),
                    Permission::caseBsnLookup(),
                    Permission::caseSearch(),
                    Permission::caseAddressLookup(),
                    Permission::caseArchive(),
                    Permission::caseReopen(),
                    Permission::caseBcoPhaseEdit(),
                    Permission::intakeList(),
                    Permission::caseArchiveDirectly(),
                    Permission::caseEditContactStatus(),
                    Permission::placeSectionList(),
                    Permission::organisationUpdate(),
                    Permission::organisationList(),
                    Permission::caseViewPlannerTimeline(),
                    Permission::caseViewOsirisHistory(),
                    Permission::choreList(),
                    Permission::datacatalog(),
                    Permission::caseMetricsList(),
                ]],
            [

                ['casequality', 'casequality_nationwide'],
                [
                    ...$caseEditPermissions,
                    Permission::caseApprove(),
                    Permission::caseArchive(),
                    Permission::caseListUserCases(),
                    Permission::caseCanBeAssignedToCompleted(),
                    Permission::caseExport(),
                    Permission::contextList(),
                    Permission::datacatalog(),

                ]],
            [['contextmanager'], []],
            [

                ['clusterspecialist'],
                [
                    ...$caseEditPermissions,
                    Permission::placeList(),
                    Permission::placeMerge(),
                    Permission::placeDelete(),
                    Permission::placeVerify(),
                    Permission::placeCaseList(),
                    Permission::placeSectionEdit(),
                    Permission::placeSectionEditOwnedByOrganisation(),
                    Permission::placeSectionDelete(),
                    Permission::placeSectionMerge(),
                    Permission::contextList(),
                    Permission::datacatalog(),
                    Permission::callcenterView(),
                    Permission::caseEditViaSearchCase(),
                    Permission::addCasesByChore(),
                ]],
            [

                ['callcenter'],
                [
                    Permission::callcenterView(),
                    Permission::datacatalog(),
                    Permission::caseCreateNote(),
                    Permission::createCallToActionViaSearchCase(),
                ]],
            [

                ['callcenter_expert'],
                [
                    ...$caseEditPermissions,
                    Permission::callcenterView(),
                    Permission::caseEditViaSearchCase(),
                    Permission::datacatalog(),
                    Permission::addCasesByChore(),
                ]],
        ];
    }
}
