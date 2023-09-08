<?php

declare(strict_types=1);

namespace Tests\Feature\Services\CaseAssignmentService;

use App\Exceptions\InvalidCaseAssignmentException;
use App\Models\Assignment\CaseListAssignment;
use App\Models\Assignment\NullOrganisationAssignment;
use App\Models\Assignment\NullUserAssignment;
use App\Models\Assignment\Option;
use App\Models\Assignment\Options;
use App\Models\Assignment\OrganisationAssignment;
use App\Models\Assignment\OrganisationOption;
use App\Models\Assignment\ReturnToOwnerOption;
use App\Models\Assignment\UserAssignment;
use App\Models\Assignment\UserOption;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\EloquentOrganisation as Organisation;
use App\Models\Eloquent\Timeline;
use App\Models\OrganisationType;
use App\Services\CaseAssignmentService;
use Carbon\CarbonImmutable;
use Illuminate\Validation\UnauthorizedException;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;
use function array_filter;
use function config;
use function is_a;
use function sprintf;

#[Group('case-assignment')]
class CaseAssignmentServiceTest extends FeatureTestCase
{
    private CaseAssignmentService $caseAssignmentService;
    private Organisation $organisation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseAssignmentService = app(CaseAssignmentService::class);
        $this->organisation = $this->createOrganisation();
    }

    #[DataProvider('userPermissionProvider')]
    public function testCaseWaitingForApprovalCanOnlyBeAssignedToUserWithApprovePermission(
        string $userRoles,
        array $caseAttributes,
        bool $expectAllowed,
    ): void {
        $case = $this->createCaseForOrganisation($this->organisation, $caseAttributes);

        $userPlanner = $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], $userRoles);
        $this->be($userPlanner);

        if (!$expectAllowed) {
            $this->expectException(InvalidCaseAssignmentException::class);
        }

        $this->caseAssignmentService->assignCase($case, new UserAssignment($userPlanner), $userPlanner);

        if (!$expectAllowed) {
            return;
        }

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_user_uuid' => $userPlanner->uuid,
        ]);
    }

    public static function userPermissionProvider(): array
    {
        return [
            'cannot assign to user if waiting for approval' => [
                'user',
                ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
                false,
            ],
            'can assign to user if not waiting for approval' => [
                'user',
                ['bco_status' => BCOStatus::open(), 'is_approved' => null],
                true,
            ],
            'can assign to casequality if waiting for approval' => [
                'casequality',
                ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
                true,
            ],
            'user,planner can assign to self' => [
                'user,planner',
                ['bco_status' => BCOStatus::open(), 'is_approved' => null],
                true,
            ],
            'user,planner can not assign to self if waiting for approval' => [
                'user,planner',
                ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
                false,
            ],
        ];
    }

    public function testCanUnassignWaitingForApprovalCase(): void
    {
        $case = $this->createCaseForOrganisation(
            $this->organisation,
            ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
        );

        $this->be($user = $this->createUserForOrganisation(
            $this->organisation,
            ['last_login_at' => CarbonImmutable::now()->subDay(1)],
            'planner',
        ));

        $this->caseAssignmentService->assignCase($case, new NullUserAssignment(), $user);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_user_uuid' => null,
        ]);
    }

    public function testCanReturnToOwnerWaitingForApprovalCase(): void
    {
        $externalOrg = $this->createOrganisation();
        $case = $this->createCaseForOrganisation(
            $this->organisation,
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => null,
                'assigned_organisation_uuid' => $externalOrg->uuid,
            ],
        );

        $this->be($user = $this->createUserForOrganisation(
            $externalOrg,
            ['last_login_at' => CarbonImmutable::now()->subDay(1)],
            'planner',
        ));

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => $externalOrg->uuid,
        ]);

        $this->caseAssignmentService->assignCase($case, new NullOrganisationAssignment());

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => null,
        ]);
    }

    public function testResetUserAndCaseListWhenReturningToOwner(): void
    {
        $externalOrg = $this->createOrganisation();
        $externalCaseList = $this->createCaseListForOrganisation($externalOrg);
        $externalUser = $this->createUserForOrganisation($externalOrg);

        $case = $this->createCaseForOrganisation(
            $this->organisation,
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => null,
                'assigned_organisation_uuid' => $externalOrg->uuid,
                'assigned_case_list_uuid' => $externalCaseList->uuid,
                'assigned_user_uuid' => $externalUser->uuid,
            ],
        );

        $this->be($this->createUserForOrganisation(
            $externalOrg,
            ['last_login_at' => CarbonImmutable::now()->subDay(1)],
            'planner',
        ));

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => $externalOrg->uuid,
            'assigned_case_list_uuid' => $externalCaseList->uuid,
            'assigned_user_uuid' => $externalUser->uuid,
        ]);

        $this->caseAssignmentService->assignCase($case, new NullOrganisationAssignment());

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
            'assigned_user_uuid' => null,
        ]);
    }

    public function testAuthenticatedUserIsInAssignmentOptionsWhenBothPlannerAndUserRole(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, [
            'bco_status' => BCOStatus::open(),
        ]);

        $userPlanner = $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], 'user,planner');
        $this->be($userPlanner);

        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);

        $foundReturnOption = false;
        foreach ($options->getSelectableOptions() as $option) {
            if ($option instanceof UserOption && $option->getUser()->uuid === $userPlanner->uuid) {
                $foundReturnOption = true;
                $this->assertEquals($userPlanner->name, $option->getLabel());
            }
        }
        $this->assertTrue($foundReturnOption);
    }

    public function testCaseCannotBeAssignedToUserWithoutCaseUserEditPermission(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $compliance = $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], 'compliance');

        $planner = $this->createUserForOrganisation($this->organisation, [], 'planner');
        $this->be($planner);

        $this->expectException(InvalidCaseAssignmentException::class);
        $this->caseAssignmentService->assignCase($case, new UserAssignment($compliance));
    }

    public function testUserWithoutCaseUserEditPermissionIsNotInAssignmentOptions(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], 'compliance');

        $planner = $this->createUserForOrganisation($this->organisation, [], 'planner');
        $this->be($planner);

        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);

        foreach ($options->getSelectableOptions() as $option) {
            $this->assertNotInstanceOf(UserOption::class, $option);
        }
    }

    public function testCaseCannotBeAssignedToUserWhoHasNotLoggedInRecently(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $user = $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDays(
                config('misc.case.assignment.lastLoginThresholdNeededForCaseAssignmentInDays') + 1,
            ),
        ]);

        $userPlanner = $this->createUserForOrganisation($this->organisation, [], 'user,planner');
        $this->be($userPlanner);

        $this->expectException(InvalidCaseAssignmentException::class);
        $this->caseAssignmentService->assignCase($case, new UserAssignment($user));
    }

    public function testUserWhoHasNotLoggedInRecentlyCannotBeInAssignmentOptions(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $user = $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDays(
                config('misc.case.assignment.lastLoginThresholdNeededForCaseAssignmentInDays') + 1,
            ),
        ]);

        $userPlanner = $this->createUserForOrganisation($this->organisation, [], 'user,planner');
        $this->be($userPlanner);

        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);

        foreach ($options->getSelectableOptions() as $option) {
            if ($option instanceof UserOption) {
                $this->assertNotEquals($option->getUser()->uuid, $user->uuid);
            }
        }
    }

    #[DataProvider('assignmentOptionsForOpenCaseAndCasequalityRoleDataProvider')]
    public function testAssignmentOptionsForOpenCaseAndCasequalityRole(
        string $roles,
        BCOStatus $bcoStatus,
        bool $optionExpectedToBeFound,
    ): void {
        $case = $this->createCaseForOrganisation(
            $this->organisation,
            ['bco_status' => $bcoStatus],
        );

        $user = $this->createUserForOrganisation($this->organisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], $roles);
        $this->be($user);

        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);

        $foundReturnOption = false;
        foreach ($options->getSelectableOptions() as $option) {
            if ($option instanceof UserOption && $option->getUser()->uuid === $user->uuid && $option->isAvailable()) {
                $foundReturnOption = true;
                $this->assertEquals($user->name, $option->getLabel());
            }
        }
        $this->assertEquals($optionExpectedToBeFound, $foundReturnOption);
    }

    public static function assignmentOptionsForOpenCaseAndCasequalityRoleDataProvider(): array
    {
        return [
            'a user with role user can be assigned to an open case' => [
                'user',
                BCOStatus::open(),
                true,
            ],
            'a user with role user_nationwide can be assigned to an open case' => [
                'user_nationwide',
                BCOStatus::open(),
                true,
            ],
            'a user with roles user and casequality can be assigned to an open case' => [
                'user,casequality',
                BCOStatus::open(),
                true,
            ],
            'a user with role casequality cannot be assigned to an open case' => [
                'casequality',
                BCOStatus::open(),
                false,
            ],
            'a user with role user cannot be assigned to a completed case' => [
                'user',
                BCOStatus::completed(),
                false,
            ],
            'a user with role user_nationwide cannot be assigned to a completed case' => [
                'user_nationwide',
                BCOStatus::completed(),
                false,
            ],
            'a user with roles user and casequality can be assigned to a completed case' => [
                'user,casequality',
                BCOStatus::completed(),
                true,
            ],
            'a user with role casequality can be assigned to a completed case' => [
                'casequality',
                BCOStatus::completed(),
                true,
            ],
        ];
    }

    #[DataProvider('organisationAssignmentDataProvider')]
    public function testCaseOrganisationAssignment(array $caseAttributes, bool $expectedValid): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, $caseAttributes);

        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
            'is_available_for_outsourcing' => true,
        ]);

        $this->organisation->outsourceOrganisations()->attach($outsourceOrganisation);
        $this->organisation->save();

        $assignment = new OrganisationAssignment($outsourceOrganisation);

        $planner = $this->createUserForOrganisation($this->organisation, [], 'planner');
        $this->be($planner);

        $this->assertSame($expectedValid, $this->caseAssignmentService->isValidAssignment($case, $assignment));
    }

    public static function organisationAssignmentDataProvider(): array
    {
        return [
            'cannot assign waiting for approval case to organisation' => [
                ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
                false,
            ],
            'can assign open case to organisation' => [
                ['bco_status' => BCOStatus::open(), 'is_approved' => null],
                true,
            ],
        ];
    }

    #[DataProvider('caseListAssignmentDataProvider')]
    public function testCaseListAssignment(array $caseAttributes, array $caseListAttributes, bool $valid): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, $caseAttributes);
        $caseList = $this->createCaseListForOrganisation($this->organisation, $caseListAttributes);

        $planner = $this->createUserForOrganisation($this->organisation, [], 'planner');
        $this->be($planner);

        $result = $this->caseAssignmentService->isValidAssignment($case, new CaseListAssignment($caseList));
        $this->assertSame($valid, $result);
    }

    public static function caseListAssignmentDataProvider(): array
    {
        return [
            'cannot assign waiting for approval to queue' => [
                ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
                ['is_queue' => true],
                false,
            ],
            'can assign waiting for approval to other lists' => [
                ['bco_status' => BCOStatus::completed(), 'is_approved' => null],
                ['is_queue' => false],
                true,
            ],
            'can assign open case to wachtrij' => [
                ['bco_status' => BCOStatus::open(), 'is_approved' => null],
                ['is_queue' => true],
                true,
            ],
            'can assign open case to other lists' => [
                ['bco_status' => BCOStatus::open(), 'is_approved' => null],
                ['is_queue' => false],
                true,
            ],
        ];
    }

    public function testOrganisationCannotBeInAssignmentOptionsWhenOutsourcingFeatureFlagIsNotEnabled(): void
    {
        config()->set('featureflag.outsourcing_enabled', false);

        $outsourceOrganisation = $this->createOrganisation([
            'is_available_for_outsourcing' => 1,
        ]);

        $organisation = $this->createOrganisation();
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case = $this->createCaseForOrganisation($organisation);

        $userPlanner = $this->createUserForOrganisation($organisation, [], 'user,planner');
        $this->be($userPlanner);

        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);

        foreach ($options->getSelectableOptions() as $option) {
            $this->assertFalse($option instanceof OrganisationOption);
        }
    }

    public function testRegionalOrganisationCannotBeInAssignmentOptionsWhenOutsourcingToRegionalGGDFeatureFlagIsNotEnabled(): void
    {
        config()->set('featureflag.outsourcing_to_regional_ggd_enabled', false);

        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);

        $organisation = $this->createOrganisation();
        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case = $this->createCaseForOrganisation($organisation);

        $userPlanner = $this->createUserForOrganisation($organisation, [], 'user,planner');
        $this->be($userPlanner);

        $options = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);

        foreach ($options->getSelectableOptions() as $option) {
            $this->assertNotInstanceOf(OrganisationOption::class, $option);
        }
    }

    /**
     * @template T
     *
     * @param class-string<T> $class
     *
     * @return T|null
     */
    private function getRootOptionOfType(Options $options, string $class): ?Option
    {
        foreach ($options->getRootOptions() as $option) {
            if (is_a($option, $class)) {
                return $option;
            }
        }

        return null;
    }

    public function testReturnToOwnerOptionSingleOwnerOrganisation(): void
    {
        $outsourceOrganisation = $this->createOrganisation();

        $outsourcePlanner = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');
        $this->be($outsourcePlanner);

        $ownerOrganisation = $this->createOrganisation();
        $ownerOrganisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case1 = $this->createCaseForOrganisation($ownerOrganisation, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $case2 = $this->createCaseForOrganisation($ownerOrganisation, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $assignmentOptions = $this->caseAssignmentService->getAssignmentOptions([$case1->uuid, $case2->uuid]);
        $returnToOwnerOption = $this->getRootOptionOfType($assignmentOptions, ReturnToOwnerOption::class);
        $this->assertNotNull($returnToOwnerOption);
        $this->assertEquals(sprintf('Verplaatsen naar %s', $ownerOrganisation->name), $returnToOwnerOption->getLabel());
    }

    public function testReturnToOwnerOptionForOwnerOrganisation(): void
    {
        $outsourceOrganisation = $this->createOrganisation();

        $ownerOrganisation = $this->createOrganisation();
        $ownerOrganisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case = $this->createCaseForOrganisation($ownerOrganisation, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $ownerPlanner = $this->createUserForOrganisation($ownerOrganisation, [], 'planner');
        $this->be($ownerPlanner);

        $assignmentOptions = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);
        $returnToOwnerOption = $this->getRootOptionOfType($assignmentOptions, ReturnToOwnerOption::class);

        $this->assertNotNull($returnToOwnerOption);
        $this->assertEquals(sprintf('Verplaatsen naar %s', $ownerOrganisation->name), $returnToOwnerOption->getLabel());
    }

    public function testReturnToOwnerOptionForOwnerOrganisationIfAssignedToUser(): void
    {
        $outsourceOrganisation = $this->createOrganisation();
        $outsourceUser = $this->createUserForOrganisation($outsourceOrganisation, [], 'user');

        $ownerOrganisation = $this->createOrganisation();
        $ownerOrganisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case = $this->createCaseForOrganisation($ownerOrganisation, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
            'assigned_user_uuid' => $outsourceUser->uuid,
        ]);

        $ownerPlanner = $this->createUserForOrganisation($ownerOrganisation, [], 'planner');
        $this->be($ownerPlanner);

        $assignmentOptions = $this->caseAssignmentService->getAssignmentOptions([$case->uuid]);
        $returnToOwnerOption = $this->getRootOptionOfType($assignmentOptions, ReturnToOwnerOption::class);

        $this->assertNull($returnToOwnerOption);
    }

    public function testReturnToOwnerOptionDifferentOwnerOrganisations(): void
    {
        $outsourceOrganisation = $this->createOrganisation();

        $outsourcePlanner = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');
        $this->be($outsourcePlanner);

        $ownerOrganisation1 = $this->createOrganisation();
        $ownerOrganisation1->outsourceOrganisations()->attach($outsourceOrganisation);

        $ownerOrganisation2 = $this->createOrganisation();
        $ownerOrganisation2->outsourceOrganisations()->attach($outsourceOrganisation);

        $caseOwner1 = $this->createCaseForOrganisation($ownerOrganisation1, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $caseOwner2 = $this->createCaseForOrganisation($ownerOrganisation2, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $assignmentOptions = $this->caseAssignmentService->getAssignmentOptions([$caseOwner1->uuid, $caseOwner2->uuid]);
        $returnToOwnerOption = $this->getRootOptionOfType($assignmentOptions, ReturnToOwnerOption::class);
        $this->assertNotNull($returnToOwnerOption);
        $this->assertEquals('Verplaatsen naar eigenaar GGDs', $returnToOwnerOption->getLabel());
    }

    public function testReturnToOwnerOptionMixOutsourcedNonOutsourcedCases(): void
    {
        $organisation1 = $this->createOrganisation();
        $organisation1Planner = $this->createUserForOrganisation($organisation1, [], 'planner');
        $this->be($organisation1Planner);

        $organisation2 = $this->createOrganisation();
        $organisation2->outsourceOrganisations()->attach($organisation1);

        $organisation1Case = $this->createCaseForOrganisation($organisation1);
        $organisation2Case = $this->createCaseForOrganisation($organisation2, [
            'assigned_organisation_uuid' => $organisation1->uuid,
        ]);

        $assignmentOptions = $this->caseAssignmentService->getAssignmentOptions([$organisation1Case->uuid, $organisation2Case->uuid]);
        $returnToOwnerOption = $this->getRootOptionOfType($assignmentOptions, ReturnToOwnerOption::class);
        $this->assertNull($returnToOwnerOption);
    }

    #[DataProvider('caseAssignNextPermissionProvider')]
    public function testAssignNextCasePermission(string $roles, bool $allowed): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $roles);
        $this->be($user);

        $caselist = $this->createCaseListForOrganisation($organisation);

        if (!$allowed) {
            $this->expectException(UnauthorizedException::class);
        }

        $result = $this->caseAssignmentService->assignNextCase($caselist);

        if ($allowed) {
            $this->assertNull($result);
        }
    }

    public static function caseAssignNextPermissionProvider(): array
    {
        return [
            'user' => ['user', true],
            'user_nationwide' => ['user_nationwide', true],
            'planner' => ['planner', false],
            'admin' => ['admin', false],
            'planner_nationwide' => ['planner_nationwide', false],
            'user_planner' => ['user_planner', false],
            'compliance' => ['compliance', false],
            'contextmanager' => ['contextmanager', false],
            'clusterspecialist' => ['clusterspecialist', false],
            'casequality' => ['casequality', false],
            'casequality_nationwide' => ['casequality_nationwide', false],
        ];
    }

    public function testGetUserAssignmentOptionsReturnsAllUsers(): void
    {
        // GIVEN an organisation has 5 users
        $organisation = $this->createOrganisation();
        // AND one of them is a planner
        foreach (['user', 'user', 'user', 'user', 'planner'] as $role) {
            $user = $this->createUserForOrganisation($organisation, ['last_login_at' => CarbonImmutable::now()], $role);
        }

        // WHEN the planner gets user assigment options
        $this->be($user);
        $options = $this->caseAssignmentService->getUserAssignmentOptions();

        // THEN the options root options is an array
        $rootOptions = $options->getRootOptions();
        $this->assertIsArray($rootOptions);
        // AND this array has all 5 users, 1 planner and 4 others
        $this->assertCount(1, array_filter($rootOptions, static fn ($o) => $o->getUser()->roles === 'planner'));
        $this->assertCount(4, array_filter($rootOptions, static fn ($o) => $o->getUser()->roles === 'user'));
    }

    public function testAssignCreatesTimelineEntry(): void
    {
        $planner = $this->createUser([], 'planner,user');
        $this->be($planner);

        $case = $this->createCaseForUser($planner);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_user_uuid' => $planner->uuid,
        ]);

        $this->caseAssignmentService->assignCase($case, new NullUserAssignment());

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_user_uuid' => null,
        ]);

        $assigment = CaseAssignmentHistory::where('covidcase_uuid', $case->uuid)->sole();

        $this->assertDatabaseHas(Timeline::class, [
            'timelineable_id' => $assigment->uuid,
            'timelineable_type' => 'case-assignment-history',
        ]);
    }

    public function testAssignNextCaseCreatesAssignmentHistory(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);

        $case = $this->createCaseForOrganisation($organisation);
        $caselist = $this->createCaseListForOrganisation($organisation, ['is_default' => true, 'is_queue' => true]);
        $caselist->cases()->save($case);

        $this->be($user);
        $result = $this->caseAssignmentService->assignNextCase($caselist);

        $this->assertSame($result, $case->uuid);
        /** @var CaseAssignmentHistory $assignment */
        $assignment = CaseAssignmentHistory::where('covidcase_uuid', $case->uuid)->sole();

        $this->assertDatabaseHas(Timeline::class, [
            'timelineable_id' => $assignment->uuid,
            'timelineable_type' => 'case-assignment-history',
        ]);
    }

    public function testAssignmentAssigner(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation);

        $this->be($user);
        $this->caseAssignmentService->assignCase($case, new NullUserAssignment());

        /** @var CaseAssignmentHistory $assignment */
        $assignment = CaseAssignmentHistory::where('covidcase_uuid', $case->uuid)->sole();
        $this->assertEquals($user->uuid, $assignment->assigned_by);
    }
}
