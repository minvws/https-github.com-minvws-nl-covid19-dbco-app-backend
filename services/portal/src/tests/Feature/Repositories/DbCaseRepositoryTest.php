<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\Eloquent\CaseLabel;
use App\Models\Eloquent\EloquentCase;
use App\Models\PlannerCase\ListOptions;
use App\Models\PlannerCase\PlannerSort;
use App\Models\PlannerCase\PlannerView;
use App\Repositories\CaseData;
use App\Repositories\DbCaseRepository;
use App\Schema\SchemaCache;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Support\Facades\Event;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\SchemaVersionDataProvider;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function array_merge;
use function collect;
use function count;
use function implode;
use function sprintf;

#[Group('case')]
class DbCaseRepositoryTest extends FeatureTestCase
{
    private DbCaseRepository $dbCaseRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dbCaseRepository = $this->app->make(DbCaseRepository::class);
    }

    #[DataProvider('createCaseWithDifferentSchemaVersionsData')]
    #[Group('override-case-version')]
    public function testCreateCaseWithDifferentSchemaVersions(int $version): void
    {
        // We need to clean the schema cache because it can already hold a schema with a specific version:
        SchemaCache::clear();

        ConfigHelper::set('schema.overrideCaseVersion', (string) $version);
        $organisation = $this->createOrganisation();

        try {
            $case = $this->dbCaseRepository->createCase(new CaseData(
                owner: null,
                bcoPhase: BCOPhase::phaseNone(),
                organisationUuid: $organisation->uuid,
                assignedCaseListUuid: null,
                organisationLabel: null,
                pseudoBsnGuid: null,
                priority: Priority::none(),
                caseLabels: [],
                testMonsterNumber: null,
                caseId: null,
                statusIndexContactTracing: null,
                automaticAddressVerificationStatus: $this->faker->randomElement(AutomaticAddressVerificationStatus::all()),
            ));

            $this->assertSame($version, $case->getSchemaVersion()->getVersion());
        } finally {
            SchemaCache::clear();
        }
    }

    public function testGetCasesByAssignedUserAndStatus(): void
    {
        $user = $this->createUser();
        $caseOpen = $this->createCaseForUser($user, [
            'bco_status' => BCOStatus::open(),
        ]);
        $this->createCaseForUser($user, [
            'bco_status' => BCOStatus::completed(),
        ]);
        $this->createCase(); // dummy case that should not show up in the results

        $dbCases = $this->dbCaseRepository->getCasesByAssignedUser($user->uuid, [BCOStatus::open()])->items();

        $this->assertCount(1, $dbCases);
        $this->assertEquals($caseOpen->uuid, $dbCases[0]->uuid);
    }

    #[DataProvider('getPlannerViewCasesDataProvider')]
    public function testGetPlannerViewCasesOrdering(
        PlannerView $plannerView,
        ?PlannerSort $sort,
        ?string $order,
        bool $listOptionsWithCaseListUuid,
        array $expectedCasePositions = [],
    ): void {
        $user = $this->createUser();
        $organisation = $user->organisations()->first();
        $caseList = $this->createCaseListForOrganisation($organisation, [
            'is_default' => true,
            'is_queue' => true,
        ]);

        // create a set of testcases, using predictable uuid's to assert ordering later
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 1,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::yesterday(),
            'bco_status' => BCOStatus::draft(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 2,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::today(),
            'bco_status' => BCOStatus::open(),
            'priority' => Priority::high(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 3,
            'created_at' => CarbonImmutable::today(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::today(),
            'bco_status' => BCOStatus::completed(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 4,
            'created_at' => CarbonImmutable::tomorrow(),
            'updated_at' => CarbonImmutable::today(),
            'date_of_test' => CarbonImmutable::today(),
            'assigned_case_list_uuid' => $caseList->uuid,
            'bco_status' => BCOStatus::open(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 5,
            'created_at' => CarbonImmutable::today(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::yesterday(),
            'assigned_case_list_uuid' => $caseList->uuid,
            'bco_status' => BCOStatus::draft(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 6,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::today(),
            'date_of_test' => CarbonImmutable::yesterday(),
            'assigned_case_list_uuid' => $caseList->uuid,
            'bco_status' => BCOStatus::open(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 7,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::today(),
            'bco_status' => BCOStatus::completed(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 9,
            'created_at' => CarbonImmutable::today(),
            'updated_at' => CarbonImmutable::today(),
            'date_of_test' => CarbonImmutable::today(),
            'bco_status' => BCOStatus::open(),
            'priority' => Priority::veryHigh(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'uuid' => 10,
            'created_at' => CarbonImmutable::tomorrow(),
            'updated_at' => CarbonImmutable::tomorrow(),
            'date_of_test' => CarbonImmutable::tomorrow(),
            'bco_status' => BCOStatus::open(),
            'priority' => Priority::veryHigh(),
        ]);

        // retrieve cases from database, using given parameters
        $listOptions = new ListOptions();
        $listOptions->view = $plannerView;
        $listOptions->sort = $sort;
        $listOptions->order = $order;
        $listOptions->caseListUuid = $listOptionsWithCaseListUuid ? $caseList->uuid : null;

        $dbCases = $this->dbCaseRepository->getPlannerViewCases($organisation, $listOptions)->items();

        $this->assertCount(count($expectedCasePositions), $dbCases);

        // if results present, assert ordering
        if (count($dbCases) <= 0) {
            return;
        }

        $expectMessage = implode(', ', $expectedCasePositions);
        $actualMessage = implode(', ', collect($dbCases)->pluck('uuid')->all());

        foreach ($expectedCasePositions as $expectedCasePosition => $createdCaseUuid) {
            $this->assertEquals(
                $createdCaseUuid,
                $dbCases[$expectedCasePosition]->uuid,
                sprintf('Invalid sort order, expected %s, got %s', $expectMessage, $actualMessage),
            );
        }
    }

    /**
     * $expectedCasePositions should contain an array with the expected ordering of the case-uuid and its position:
     * [2 => 4]: be on position 4 of the results, you should find the (created) case with uuid 2
     */
    public static function getPlannerViewCasesDataProvider(): array
    {
        return [
            'queued, no sort, no caselist' => [PlannerView::queued(), null, null, false, [6, 5, 4]],
            'queued, sort createdAt, no caselist' => [PlannerView::queued(), PlannerSort::createdAt(), null, false, [6, 5, 4]],
            'assigned, sort createdAt, no caselist' => [PlannerView::assigned(), PlannerSort::createdAt(), null, false],
            'assigned, no sort, no caselist' => [PlannerView::assigned(), null, null, false],
            'assigned, no sort, with caselist' => [PlannerView::assigned(), null, null, true],
            'completed, no sort, no caselist' => [PlannerView::completed(), null, null, false, [3, 7]],
            'completed, no sort, with caselist' => [PlannerView::completed(), null, null, true],
            'completed, sort createdAt, no caselist' => [PlannerView::completed(), PlannerSort::createdAt(), null, false, [7, 3]],
            'unassigned, no sort, no caselist' => [PlannerView::unassigned(), null, null, false, [1, 2, 9, 10]],
            'unassigned, sort createdAt, no caselist' => [
                PlannerView::unassigned(),
                PlannerSort::createdAt(),
                null,
                false,
                [1, 2, 9, 10],
            ],
            'unassigned, sort createdAt, no caselist asc' => [
                PlannerView::unassigned(),
                PlannerSort::createdAt(),
                'asc',
                false,
                [1, 2, 9, 10],
            ],
            'unassigned, sort createdAt, no caselist desc' => [
                PlannerView::unassigned(),
                PlannerSort::createdAt(),
                'desc',
                false,
                [10, 9, 1, 2],
            ],
            'unassigned, sort updatedAt, no caselist' => [
                PlannerView::unassigned(),
                PlannerSort::updatedAt(),
                null,
                false,
                [1, 2, 9, 10],
            ],
            'unassigned, sort updatedAt asc, no caselist' => [
                PlannerView::unassigned(),
                PlannerSort::updatedAt(),
                'asc',
                false,
                [1, 2, 9, 10],
            ],
            'unassigned, sort updatedAt desc, no caselist' => [
                PlannerView::unassigned(),
                PlannerSort::updatedAt(),
                'desc',
                false,
                [10, 9, 1, 2],
            ],
            'unassigned, sort priority, no caselist' => [
                PlannerView::unassigned(),
                PlannerSort::priority(),
                null,
                false,
                [1, 2, 9, 10],
            ],
            'unassigned, sort priority, no caselist asc' => [
                PlannerView::unassigned(),
                PlannerSort::priority(),
                'asc',
                false,
                [1, 2, 9, 10],
            ],
            'unassigned, sort priority, no caselist desc' => [
                PlannerView::unassigned(),
                PlannerSort::priority(),
                'desc',
                false,
                [ 9, 10, 2, 1],
            ],
        ];
    }

    #[DataProvider('assignNextCaseProvider')]
    public function testAssignNextCase(array $seeds, array $expectedUuids): void
    {
        $user = $this->createUser();
        $organisation = $user->organisations()->first();
        $list = $this->createCaseListForOrganisation($organisation);
        foreach ($seeds as $seed) {
            $this->createCaseForOrganisation($organisation, array_merge([
                'assigned_case_list_uuid' => $list->uuid,
                'updated_at' => CarbonImmutable::today(),
                'bco_status' => BCOStatus::open(),
            ], $seed));
        }

        foreach ($expectedUuids as $expectedUuid) {
            $uuid = $this->dbCaseRepository->assignNextCase($list->uuid, $user->uuid);
            $this->assertSame($expectedUuid, $uuid);
        }
    }

    public static function assignNextCaseProvider(): Generator
    {
        yield "priority takes precedence" => [
            [
                [
                    'uuid' => 'yesterday|none',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => CarbonImmutable::yesterday(),
                ],
                [
                    'uuid' => 'today|high',
                    'priority' => Priority::high(),
                    'date_of_test' => CarbonImmutable::today(),
                    'created_at' => CarbonImmutable::today(),
                ],
                [
                    'uuid' => 'today|veryhigh',
                    'priority' => Priority::veryHigh(),
                    'date_of_test' => CarbonImmutable::today(),
                    'created_at' => CarbonImmutable::today(),
                ],
                [
                    'uuid' => 'yesterday|normal',
                    'priority' => Priority::normal(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => CarbonImmutable::yesterday(),
                ],
            ],
            ['today|veryhigh', 'today|high', 'yesterday|normal', 'yesterday|none'],
        ];

        yield "date of test if equal prio" => [
            [
                [
                    'uuid' => 'dot|yesterday',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => CarbonImmutable::today(),
                ],
                [
                    'uuid' => 'dot|today',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::today(),
                    'created_at' => CarbonImmutable::today(),
                ],
                [
                    'uuid' => 'dot|2 days ago',
                    'priority' => Priority::none(),
                    'date_of_test' => new CarbonImmutable('-2 days'),
                    'created_at' => CarbonImmutable::today(),
                ],
            ],
            ['dot|2 days ago', 'dot|yesterday', 'dot|today'],
        ];

        yield "date of creation if equal prio and testdate" => [
            [
                [
                    'uuid' => 'doc|yesterday',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => CarbonImmutable::yesterday(),
                ],
                [
                    'uuid' => 'doc|today',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => CarbonImmutable::today(),
                ],
                [
                    'uuid' => 'doc|2 days ago',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => new CarbonImmutable('-2 days'),
                ],
            ],
            ['doc|2 days ago', 'doc|yesterday', 'doc|today'],
        ];
    }

    #[DataProvider('bcoListSortingProvider')]
    public function testBcoListSorting(array $seeds, array $expectedUuids): void
    {
        $user = $this->createUser();

        foreach ($seeds as $seed) {
            $this->createCaseForUser($user, array_merge([
                'bco_status' => BCOStatus::open(),
            ], $seed));
        }

        $cases = $this->dbCaseRepository->getCasesByAssignedUser($user->uuid)->items();
        foreach ($expectedUuids as $i => $expectedUuid) {
            $this->assertSame($expectedUuid, $cases[$i]->uuid);
        }
    }

    public static function bcoListSortingProvider(): Generator
    {
        yield "sorted by last updated_at" => [
            [
                [
                    'uuid' => 'bcoSort|upd|today_1',
                    'priority' => Priority::none(),
                    'date_of_test' => CarbonImmutable::yesterday(),
                    'created_at' => CarbonImmutable::yesterday(),
                    'updated_at' => CarbonImmutable::today(),
                ],
                [
                    'uuid' => 'bcoSort|upd|now',
                    'priority' => Priority::high(),
                    'date_of_test' => CarbonImmutable::today(),
                    'created_at' => CarbonImmutable::today(),
                    'updated_at' => CarbonImmutable::now(),
                ],
                [
                    'uuid' => 'bcoSort|upd|yesterday',
                    'priority' => Priority::veryHigh(),
                    'date_of_test' => CarbonImmutable::today(),
                    'created_at' => CarbonImmutable::today(),
                    'updated_at' => CarbonImmutable::yesterday(),
                ],
            ],
            ['bcoSort|upd|now', 'bcoSort|upd|today_1', 'bcoSort|upd|yesterday'],
        ];
    }

    #[DataProvider('approvalFilterDataProvider')]
    public function testApprovalFilter(
        PlannerView $plannerView,
        array $caseAttributes,
        bool $expectedNumberOfCases,
    ): void {
        $user = $this->createUser();
        $organisation = $user->organisations()->first();

        // create a set of testcases, using predictable uuid's to assert ordering later
        $this->createCaseForOrganisation($organisation, array_merge([
            'uuid' => 1,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::yesterday(),
            'bco_status' => BCOStatus::draft(),
        ], $caseAttributes));


        // retrieve cases from database, using given parameters
        $listOptions = new ListOptions();
        $listOptions->view = $plannerView;

        $dbCases = $this->dbCaseRepository->getPlannerViewCases($organisation, $listOptions)->items();

        if ($expectedNumberOfCases) {
            $this->assertCount(1, $dbCases);
        } else {
            $this->assertCount(0, $dbCases);
        }
    }

    public static function approvalFilterDataProvider(): array
    {
        return [
            'completed returns cases waiting for approval' => [
                PlannerView::completed(),
                [
                    'bco_status' => BCOStatus::completed(),
                    'is_approved' => null,
                ],
                true,
            ],
            'completed does not contain disapproved cases' => [
                PlannerView::completed(),
                [
                    'bco_status' => BCOStatus::completed(),
                    'is_approved' => false,
                ],
                false,
            ],
            'completed does not contain approved cases' => [
                PlannerView::completed(),
                [
                    'bco_status' => BCOStatus::completed(),
                    'is_approved' => true,
                ],
                false,
            ],
            'unassigned contains case if bco status is draft' => [
                PlannerView::unassigned(),
                [
                    'bco_status' => BCOStatus::draft(),
                    'is_approved' => null,
                    'assigned_user_uuid' => null,
                ],
                true,
            ],
            'unassigned contains case if bco status is open' => [
                PlannerView::unassigned(),
                [
                    'bco_status' => BCOStatus::open(),
                    'is_approved' => null,
                ],
                true,
            ],
            'unassigned contains case if bco status is completed and disapproved' => [
                PlannerView::unassigned(),
                [
                    'bco_status' => BCOStatus::completed(),
                    'is_approved' => false,
                ],
                true,
            ],
            'unassigned does not contain case if bco status is other and not unapproved' => [
                PlannerView::unassigned(),
                [
                    'bco_status' => BCOStatus::unknown(),
                    'is_approved' => false,
                ],
                false,
            ],
            'unassigned does not contain case if bco status is completed and unapproved' => [
                PlannerView::unassigned(),
                [
                    'bco_status' => BCOStatus::completed(),
                    'is_approved' => null,
                ],
                false,
            ],
            'unassigned does not contain case if bco status is completed and approved' => [
                PlannerView::unassigned(),
                [
                    'bco_status' => BCOStatus::completed(),
                    'is_approved' => true,
                ],
                false,
            ],
        ];
    }

    #[DataProvider('casesInCorrectTabsDataProvider')]
    public function testCasesInCorrectTabsWithCaseList(
        PlannerView $plannerView,
        array $caseAttributes,
        bool $assignedToCaseList,
        bool $viewHasCase,
    ): void {
        $user = $this->createUser();
        $organisation = $user->organisations()->first();

        if ($assignedToCaseList) {
            $caseListUuid = $this->createCaseListForOrganisation($organisation)->uuid;
        }

        // create a set of testcases, using predictable uuid's to assert ordering later
        $this->createCaseForOrganisation($organisation, array_merge([
            'uuid' => 1,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::yesterday(),
            'bco_status' => BCOStatus::draft(),
            'assigned_case_list_uuid' => $caseListUuid ?? null,
        ], $caseAttributes));

        // retrieve cases from database, using given parameters
        $listOptions = new ListOptions();
        $listOptions->view = $plannerView;

        $dbCases = $this->dbCaseRepository->getPlannerViewCases($organisation, $listOptions)->items();

        $this->assertCount($viewHasCase ? 1 : 0, $dbCases);
    }

    public static function casesInCorrectTabsDataProvider(): array
    {
        return [
            'Completed view contains Unassigned case' => [
                PlannerView::completed(),
                [
                    'bco_status' => BCOStatus::completed(),
                ],
                false,
                true,
            ],
            'Completed view does not contain assigned case' => [
                PlannerView::completed(),
                [
                    'bco_status' => BCOStatus::completed(),
                ],
                true,
                false,
            ],
            'Archived view contains Unassigned case' => [
                PlannerView::archived(),
                [
                    'bco_status' => BCOStatus::archived(),
                ],
                false,
                true,
            ],
            'Archived view contains Assigned case (set to list)' => [
                PlannerView::archived(),
                [
                    'bco_status' => BCOStatus::archived(),
                ],
                true,
                true,
            ],
        ];
    }

    #[DataProvider('currentOrganisationFilterDataProvider')]
    public function testCurrentOrganisationFilter(
        PlannerView $plannerView,
        array $caseAttributes,
        array $organisationAttributes,
        int $expectResult,
    ): void {
        $organisation = $this->createOrganisation($organisationAttributes);

        $this->createCase(array_merge([
            'uuid' => 1123,
            'created_at' => CarbonImmutable::yesterday(),
            'updated_at' => CarbonImmutable::yesterday(),
            'date_of_test' => CarbonImmutable::yesterday(),
            'assigned_user_uuid' => null,
            'assigned_case_list_uuid' => null,
            'deleted_at' => null,
            'bco_status' => BCOStatus::draft(),
        ], $caseAttributes));

        // retrieve cases from database, using given parameters
        $listOptions = new ListOptions();
        $listOptions->view = $plannerView;

        $dbCases = $this->dbCaseRepository->getPlannerViewCases($organisation, $listOptions)->items();

        $this->assertCount($expectResult, $dbCases);
    }

    public static function currentOrganisationFilterDataProvider(): array
    {
        return [
            'archived filters current on organisation' => [
                PlannerView::archived(),
                [
                    'bco_status' => BCOStatus::archived(),
                    'is_approved' => true,
                    'current_organisation_uuid' => '123',
                ],
                [
                    'uuid' => 'current_uuid1',
                ],
                0,
            ],
        ];
    }

    #[DataProvider('withLabelsDataProvider')]
    public function testGetPlannerViewCasesWithLabelFilter(
        ?string $label,
        int $expectedResults,
    ): void {
        $organisation = $this->createOrganisation();
        CaseLabel::factory()->create(['uuid' => 'foo']);
        $bar = CaseLabel::factory()->create(['uuid' => 'bar']);
        $baz = CaseLabel::factory()->create(['uuid' => 'baz']);
        CaseLabel::factory()->create(['uuid' => 'not-applied']);

        foreach ([0, 0, 0, 1, 1, 2] as $numOfLabels) {
            $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::draft()]);
            if ($numOfLabels > 1) {
                $case->caseLabels()->attach($bar);
            }
            if ($numOfLabels > 0) {
                $case->caseLabels()->attach($baz);
            }
        }

        $listOptions = new ListOptions();
        $listOptions->view = PlannerView::unassigned();
        $listOptions->label = $label;
        $dbCases = $this->dbCaseRepository->getPlannerViewCases($organisation, $listOptions)->items();

        $this->assertCount($expectedResults, $dbCases);
    }

    public static function withLabelsDataProvider(): array
    {
        return [
            'no label provided returns all' => [null, 6],
            'non existing label provided returns none' => ['does-not-exist', 0],
            'existing label provided which is not applied returns none' => ['foo', 0],
            'existing label provided which is applied to one returns one' => ['bar', 1],
            'existing label provided which is applied to three returns three' => ['baz', 3],
        ];
    }

    public function testGetPlannerViewCasesEmptyLastAssignedUserIfUserAssigned(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $case->refresh();
        $listOptions = new ListOptions();
        $listOptions->view = PlannerView::from($case->current_organisation_planner_view);

        $dbCases = $this->dbCaseRepository->getPlannerViewCases($user->organisations->first(), $listOptions)->items();

        $this->assertCount(1, $dbCases);
        $this->assertNull($dbCases[0]->last_assigned_user_name);
    }

    public function testGetPlannerViewCasesEmptyLastAssignedUserIfNeverAssigned(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForOrganisation($user->organisations->first(), ['assigned_user_uuid' => null]);
        $case->refresh();
        $listOptions = new ListOptions();
        $listOptions->view = PlannerView::from($case->current_organisation_planner_view);

        $dbCases = $this->dbCaseRepository->getPlannerViewCases($user->organisations->first(), $listOptions)->items();

        $this->assertCount(1, $dbCases);
        $this->assertNull($dbCases[0]->last_assigned_user_name);
    }

    #[DataProvider('provideAssignmentHistoryData')]
    public function testGetPlannerViewCasesHasLastAssignedUser(bool $isAssignedToOrganisation, bool $isAssignedToCaseList): void
    {
        $assignedAt = CarbonImmutable::parse($this->faker->dateTimeBetween('-2 months'));
        $user = $this->createUser();
        $case = $this->createCaseForOrganisation($user->organisations->first(), ['assigned_user_uuid' => null]);

        $this->createAssignmentHistoryForCase($case, [
            'assigned_at' => $assignedAt,
            'assigned_user_uuid' => $user->uuid,
            'assigned_organisation_uuid' => null,
            'assigned_case_list_uuid' => null,
        ]);
        $this->createAssignmentHistoryForCase($case, [
            'assigned_at' => $assignedAt->addDay(),
            'assigned_user_uuid' => null,
            'assigned_organisation_uuid' => $isAssignedToOrganisation ? $this->createOrganisation()->uuid : null,
            'assigned_case_list_uuid' => $isAssignedToCaseList ? $this->createCaseList()->uuid : null,
        ]);

        $case->refresh();
        $listOptions = new ListOptions();
        $listOptions->view = PlannerView::from($case->current_organisation_planner_view);
        $dbCases = $this->dbCaseRepository->getPlannerViewCases($user->organisations->first(), $listOptions)->items();

        $this->assertCount(1, $dbCases);
        $this->assertEquals($user->name, $dbCases[0]->last_assigned_user_name);
    }

    public static function provideAssignmentHistoryData(): Generator
    {
        yield 'case previously assigned to user, but now no assignee' => [false, false];
        yield 'case previously assigned to user, but now assigned organisation' => [true, false];
        yield 'case previously assigned to user, but now assigned case list' => [false, true];
    }

    #[DataProvider('getCasesByUuidProvider')]
    public function testGetCasesByUuids(
        ?bool $withCaseAuthScope,
        int $numberOfCases,
    ): void {
        $outsourceOrganisation = $this->createOrganisation();

        $ownerOrganisation = $this->createOrganisation();
        $ownerOrganisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case1 = $this->createCaseForOrganisation($ownerOrganisation, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $case2 = $this->createCaseForOrganisation($outsourceOrganisation, [
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        $ownerPlanner = $this->createUserForOrganisation($ownerOrganisation, [], 'planner');
        $this->be($ownerPlanner);

        $cases = $this->dbCaseRepository->getCasesByUuids([$case1->uuid, $case2->uuid], [], ['*'], $withCaseAuthScope);

        $this->assertCount($numberOfCases, $cases);
    }

    /**
     * @see testGetCasesByUuids
     */
    public static function getCasesByUuidProvider(): Generator
    {
        yield 'with CaseAuthScope' => [true, 1];
        yield 'without CaseAuthScope' => [false, 2];
    }

    #[DataProvider('isApprovedResponseDataProvider')]
    public function testIsApprovedResponse(
        ?bool $isApproved,
    ): void {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'is_approved' => $isApproved,
        ]);

        $repositoryCase = $this->dbCaseRepository->getCase($case->uuid);
        $this->assertEquals($repositoryCase->isApproved, $isApproved);
    }

    public static function isApprovedResponseDataProvider(): Generator
    {
        yield 'isApproved is `null`' => [null];
        yield 'isApproved is `true`' => [true];
        yield 'isApproved is `false`' => [false];
    }

    public function testCaseIsCanUnassignCaseList(): void
    {
        // Create case with assigned case list
        $caseList = $this->createCaseList();
        $case = $this->createCase([
            'assigned_case_list_uuid' => $caseList->uuid,
        ]);

        // Unassign case list
        $this->dbCaseRepository->unassignCaseListOnCase($case);

        // Assert case list is null
        $this->assertNull($case->assigned_case_list_uuid);
    }

    public function testCaseFromEloquentModelWhenCaseInDatabaseHasNoOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $dbCase = $this->createCase([
            'bco_phase' => $this->faker->randomElement(BCOPhase::all()),
            'organisation_uuid' => $organisation->uuid,
            'assigned_organisation_uuid' => $organisation->uuid,
        ]);
        $dbCase->organisation = null;
        $case = $this->dbCaseRepository->caseFromEloquentModel($dbCase);

        self::assertEquals($case->organisation->uuid, $organisation->uuid);
    }

    public function testSaveQuietlyWithoutTimestamps(): void
    {
        $updatedAt = $this->faker->dateTime();

        $case = $this->createCase([
            'source' => 'foo',
            'updated_at' => $updatedAt,
        ]);

        // make sure case isDirty by changing a property, otherwise it skips the database-save
        $case->source = 'bar';

        Event::fake();

        $this->dbCaseRepository->saveQuietlyWithoutTimestamps($case);

        Event::assertNothingDispatched();

        $case->refresh();
        $this->assertEquals($updatedAt, $case->updatedAt);
    }

    public static function createCaseWithDifferentSchemaVersionsData(): array
    {
        return SchemaVersionDataProvider::all(EloquentCase::class);
    }
}
