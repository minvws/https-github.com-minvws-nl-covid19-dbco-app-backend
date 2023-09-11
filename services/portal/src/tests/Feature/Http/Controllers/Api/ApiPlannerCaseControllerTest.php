<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\General;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\IndexAddress;
use App\Models\Eloquent\CaseList;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\OrganisationType;
use App\Models\PlannerCase\PlannerSort;
use App\Models\PlannerCase\PlannerView;
use App\Models\StatusIndexContactTracing;
use App\Repositories\DbCaseRepository;
use App\Schema\SchemaCache;
use App\Schema\SchemaObject;
use App\Schema\SchemaVersion;
use App\Schema\Types\SchemaType;
use App\Services\CaseFragmentService;
use App\Services\Note\CaseNoteService;
use Carbon\CarbonImmutable;
use Exception;
use Generator;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Support\Facades\Queue;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use MinVWS\DBCO\Enum\Models\TestResultSource;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\DataProvider\SchemaVersionDataProvider;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;
use TRegx\PhpUnit\DataProviders\DataProvider as TRegxDataProvider;

use function app;
use function array_merge;
use function collect;
use function http_build_query;
use function sleep;
use function sprintf;

#[Group('planner-case')]
#[Group('case')]
class ApiPlannerCaseControllerTest extends FeatureTestCase
{
    private const VALID_CASE_DATA = [
        'index' => [
            'address' => [
                'postalCode' => '',
                'street' => '',
                'houseNumber' => '',
                'houseNumberSuffix' => '',
                'town' => '',
            ],
            'firstname' => 'Luella',
            'lastname' => 'Graham',
            'dateOfBirth' => '1986-11-25',
        ],
        'contact' => [
            'phone' => '0612345678',
            'email' => 'Joe81@hotmail.com',
        ],
        'general' => [
            'hpzone_number' => '4912983',
        ],
        'test' => [
            'dateOfTest' => '',
        ],
    ];

    private Config $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = $this->app->make(Config::class);
    }

    #[DataProvider('createNewCasePermissionsProvider')]
    public function testCreateNewCasePermissions(
        string $userRole,
        int $expectedStatus,
    ): void {
        $user = $this->createUser([], $userRole);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
                'notes' => 'a note',
            ],
        ];

        $response = $this->be($user)->postJson('/api/cases', $caseData);
        $this->assertStatus($response, $expectedStatus);
    }

    public static function createNewCasePermissionsProvider(): array
    {
        return [
            'planner regional' => ['planner', 201],
            'planner nationwide' => ['planner_nationwide', 403],
            'planner & user' => ['planner,user', 201],
            'user' => ['user', 201],
            'user_nationwide' => ['user_nationwide', 403],
            'compliance' => ['compliance', 403],
        ];
    }

    public function testCreateRetrieveAndEditNewCaseAsUser(): void
    {
        $organisation = $this->createOrganisation(['type' => OrganisationType::regionalGGD()]);
        $user = $this->createUserForOrganisation($organisation);
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $userPlanner = $this->createUserForOrganisation($organisation, [], 'user,planner');

        // create case without data
        $response = $this->be($planner)->postJson('/api/cases', []);
        $this->assertStatus($response, 422);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
                'bsn' => '123456789',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
                'notes' => 'a note',
            ],
        ];

        $expectedCaseData = $caseData;
        unset($expectedCaseData['index']['bsn']);

        $response = $this->postJson('/api/cases', $caseData);

        $this->assertStatus($response, 201);
        $data = $response->json();
        $this->assertNotEmpty($data['data']['uuid']);

        $caseUuid = $data['data']['uuid'];

        /** @var EloquentCase $case */
        $case = EloquentCase::query()->findOrFail($caseUuid);
        $case->assigned_user_uuid = $user->uuid;
        $case->save();

        // a case assigned to a user is not editable by the planner anymore ...
        $response = $this->be($userPlanner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 403);
        $response = $this->be($planner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 403);

        $case->assigned_user_uuid = $userPlanner->uuid;
        $case->save();

        // ... unless the planner *is* the assigned user
        $response = $this->be($userPlanner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 200);
        $response->assertJson(['data' => $expectedCaseData]);
        $this->assertFalse(isset($response->json('data')['index']['bsn']));

        // however, a different planner is of course still not allowed to edit the case
        $response = $this->be($planner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 403);

        $case->assigned_user_uuid = null;
        $case->save();

        // unassigned both planners are allowed to edit the case
        $response = $this->be($userPlanner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 200);
        $response = $this->be($planner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 200);

        $updatedCaseData = [
            'contact' => [
                'phone' => '06 87654321',
            ],
            'general' => [
                'notes' => 'an updated note',
                'hpzoneNumber' => '1234567',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
        ];
        $expectedCaseDataAfterUpdate = $expectedCaseData;
        $expectedCaseDataAfterUpdate['contact'] = array_merge($expectedCaseData['contact'], $updatedCaseData['contact']);
        $expectedCaseDataAfterUpdate['general'] = array_merge($expectedCaseData['general'], $updatedCaseData['general']);
        $response = $this->putJson('/api/cases/planner/' . $caseUuid, $updatedCaseData);
        $this->assertStatus($response, 200);
        $response->assertJson(['data' => $expectedCaseDataAfterUpdate]);

        // check if we are allowed to retrieve/update as owner after new case edit period has expired
        $case->assigned_user_uuid = $user->uuid;
        $case->save();
        CarbonImmutable::setTestNow('+25 hours');

        $response = $this->be($planner)->getJson('/api/cases/planner/' . $caseUuid);
        $this->assertStatus($response, 403);

        $response = $this->be($planner)->putJson('/api/cases/planner/' . $caseUuid, $updatedCaseData);
        $this->assertStatus($response, 403);
    }

    /**
     * Test if Case reference (HPZone id) is unique.
     *
     * Note: The reference is stored in the case.general fragment under the property 'reference'. In the
     * covidecase tabel it is stored in the field 'case_id'.
     */
    public function testPostCaseWithReferenceWhichIsNotUniqueShouldFail(): void
    {
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);
        $this->createCase([
            'case_id' => '1234567',
            'bco_status' => BCOStatus::open(),
        ]);

        $organisation = $planner->organisations->first();

        // create case with duplicate reference
        $phone = '06-12345678';
        $dateOfTest = CarbonImmutable::yesterday()->format('Y-m-d');
        $response = $this->be($planner)->postJson('/api/cases', [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
                'bsn' => '123456789',
            ],
            'contact' => [
                'phone' => $phone,
            ],
            'test' => [
                'dateOfTest' => $dateOfTest,
            ],
            'general' => [
                'organisation' => [
                    'uuid' => $organisation->uuid,
                ],
                'reference' => '1234567',
                'notes' => '...',
            ],
        ]);

        $this->assertStatus($response, 422);
        $data = $response->json();
        $this->assertArrayHasKey('general.reference', $data['validationResult']['fatal']['errors']);
    }

    #[DataProvider('deleteProvider')]
    #[Group('planner-case-delete')]
    public function testCaseDeletion(
        bool $setAssignedOrganisationUuid,
        bool $setAssignedUserUuid,
        BCOStatus $bcoStatus,
        int $expectedStatusCode,
    ): void {
        $user = $this->createUser();
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);
        $organisation = $planner->organisations->first();

        $assignedOrganisationUuid = $setAssignedOrganisationUuid ? $organisation->uuid : null;
        $assignedUserUuid = $setAssignedUserUuid ? $user->uuid : null;

        $case = $this->createCaseForUser($planner, [
            'assigned_organisation_uuid' => $assignedOrganisationUuid,
            'assigned_user_uuid' => $assignedUserUuid,
            'bco_status' => $bcoStatus,
        ]);

        $response = $this->be($planner)->delete('/api/cases/' . $case->uuid);
        $this->assertStatus($response, $expectedStatusCode);

        // after successful deletion we expect a 404
        if ($expectedStatusCode !== 204) {
            return;
        }

        $response = $this->be($planner)->delete('/api/cases/' . $case->uuid);
        $this->assertStatus($response, 404);
    }

    public static function deleteProvider(): array
    {
        return [
            'Unassigned and BCO status is draft' => [false, false, BCOStatus::draft(), 204],
            'Assigned to organisation and BCO status is draft' => [true, false, BCOStatus::draft(), 403],
            'Assigned to user and BCO status is draft' => [false, true, BCOStatus::draft(), 403],
            'Unassigned and BCO status is open' => [false, false, BCOStatus::open(), 403],
            'Unassigned and BCO status is completed' => [false, false, BCOStatus::completed(), 403],
            'Unassigned and BCO status is archived' => [false, false, BCOStatus::archived(), 403],
        ];
    }

    #[Group('planner-case-delete')]
    public function testCaseDeletionThrows404IfCaseNotFound(): void
    {
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);

        $response = $this->be($planner)->delete('/api/cases/' . $this->faker->uuid());
        $this->assertStatus($response, 404);
    }

    #[DataProvider('filteringAndPaginationProvider')]
    public function testFilteringAndPagination(
        string $userRole,
        ?string $caseList,
        string $view,
        ?int $perPage,
        ?int $page,
        int $expectedTotal,
        int $expectedDataCount,
    ): void {
        // create entities for testdata
        $organisations = [];
        $organisations['organisation'] = $this->createOrganisation();
        $organisations['outsourceOrganisation'] = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $organisations['organisation']->outsourceOrganisations()->attach($organisations['outsourceOrganisation']);

        $users = [];
        $users['user'] = $this->createUserForOrganisation($organisations['organisation'], [], 'user');
        $users['planner'] = $this->createUserForOrganisation($organisations['organisation'], [], 'planner');
        $users['outsourceUser'] = $this->createUserForOrganisation($organisations['outsourceOrganisation'], [], 'user_nationwide');
        $users['outsourcePlanner'] = $this->createUserForOrganisation($organisations['outsourceOrganisation'], [], 'planner_nationwide');

        $caseLists = [];
        $caseLists['caseList1'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 1,
            'is_queue' => 1,
        ]);
        $caseLists['caseList2'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);
        $caseLists['caseList3'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);

        $caseLists['outsourceCaseList1'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 1,
            'is_queue' => 1,
        ]);
        $caseLists['outsourceCaseList2'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);
        $caseLists['outsourceCaseList3'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);

        $this->createTestCases($users, $organisations, $caseLists);

        // make request
        $query = [];
        if ($perPage !== null) {
            $query['perPage'] = $perPage;
        }
        if ($page !== null) {
            $query['page'] = $page;
        }
        $query['includeTotal'] = 1;
        $uriCaseList = $caseList ? sprintf('caselists/%s/', $caseLists[$caseList]->uuid) : '';
        $uri = sprintf('/api/%scases/%s?%s', $uriCaseList, $view, http_build_query($query));
        $response = $this->be($users[$userRole])->getJson($uri);

        // assert response
        $this->assertStatus($response, 200);
        $this->assertEquals($expectedTotal, $response->json('total'));
        $this->assertCount($expectedDataCount, $response->json('data'));

        foreach ($response->json('data') as $case) {
            if ($view === 'unassigned' && $caseList === null) {
                $this->assertFalse(isset($case['assignedCaseList']['name']) || isset($case['assignedUser']['name']));
            } elseif ($view === 'unassigned') {
                $this->assertFalse(isset($case['assignedUser']['name']));
            } elseif ($view === 'assigned') {
                $this->assertTrue(
                    isset($case['assignedOrganisation']['name']) || isset($case['assignedCaseList']['name']) || isset($case['assignedUser']['name']),
                );
            } elseif ($view === 'queued') {
                $this->assertTrue(isset($case['assignedCaseList']['name']));
                $this->assertTrue($case['assignedCaseList']['isQueue']);
            }
        }
    }

    /**
     * @param array<EloquentUser> $users
     * @param array<EloquentOrganisation> $organisations
     * @param array<CaseList> $caseLists
     */
    private function createTestCases(
        array $users,
        array $organisations,
        array $caseLists,
    ): void {
        for ($i = 1; $i <= 31; $i++) {
            $caseAttributes = [
                'created_at' => (new CarbonImmutable('now'))->day(-($i * 2)),
                'updated_at' => (new CarbonImmutable('now'))->day(-$i),
                'organisation_uuid' => $organisations['organisation']->uuid,
                'date_of_test' => (new CarbonImmutable('now'))->day(-$i),
                'bco_status' => BCOStatus::open(),
                'case_id' => sprintf('10000%02d', $i),
            ];

            if ($i <= 3) { // assign 3 cases to demo user
                $caseAttributes['assigned_user_uuid'] = $users['user']->uuid;
            } elseif ($i <= 5) { // assign 2 cases to demo user/planner
                $caseAttributes['assigned_user_uuid'] = $users['planner']->uuid;
            } elseif ($i <= 8) { // assign 3 cases to default case queue
                $caseAttributes['assigned_case_list_uuid'] = $caseLists['caseList1']->uuid;
            } elseif ($i <= 10) { // assign 2 cases to first non-default case list
                $caseAttributes['assigned_case_list_uuid'] = $caseLists['caseList2']->uuid;
            } elseif ($i <= 11) { // assign 1 case to first non-default case list and assigned to user
                $caseAttributes['assigned_case_list_uuid'] = $caseLists['caseList2']->uuid;
                $caseAttributes['assigned_user_uuid'] = $users['user']->uuid;
            } elseif ($i <= 16) { // assign 5 cases to outsource organisation with no case list / no user
                $caseAttributes['assigned_organisation_uuid'] = $organisations['outsourceOrganisation']->uuid;
            } elseif ($i <= 18) { // assign 2 cases to outsource organisation default queue
                $caseAttributes['assigned_organisation_uuid'] = $organisations['outsourceOrganisation']->uuid;
                $caseAttributes['assigned_case_list_uuid'] = $caseLists['outsourceCaseList1']->uuid;
            } elseif ($i <= 20) { // assign 2 cases to outsource organisation first case list
                $caseAttributes['assigned_organisation_uuid'] = $organisations['outsourceOrganisation']->uuid;
                $caseAttributes['assigned_case_list_uuid'] = $caseLists['outsourceCaseList2']->uuid;
            } elseif ($i <= 21) { // assign 1 case to outsource organisation 2nd case list
                $caseAttributes['assigned_organisation_uuid'] = $organisations['outsourceOrganisation']->uuid;
                $caseAttributes['assigned_case_list_uuid'] = $caseLists['outsourceCaseList3']->uuid;
            } elseif ($i <= 23) { // assign 2 cases to outsource user
                $caseAttributes['assigned_organisation_uuid'] = $organisations['outsourceOrganisation']->uuid;
                $caseAttributes['assigned_user_uuid'] = $users['outsourceUser']->uuid;
            } elseif ($i <= 26) { // make the outsource organisation owner of 3 cases
                $caseAttributes['organisation_uuid'] = $organisations['outsourceOrganisation']->uuid;
            } elseif ($i <= 27) { // 1 completed
                $caseAttributes['bco_status'] = BCOStatus::completed();
            } elseif ($i <= 28) { // 1 completed
                $caseAttributes['bco_status'] = BCOStatus::archived();
                $caseAttributes['updatedAt'] = CarbonImmutable::now();
            }
            // leave 3 unassigned

            $this->createCase($caseAttributes);
        }
    }

    public static function filteringAndPaginationProvider(): array
    {
        return [
            // demo organisation
            'demo_unassigned' => ['planner', null, 'unassigned', null, null, 3, 3],
            'demo_unassigned_20_per_page_page_1' => ['planner', null, 'unassigned', 20, 1, 3, 3],
            'demo_unassigned_20_per_page_page_2' => ['planner', null, 'unassigned', 20, 2, 3, 0],
            'demo_unassigned_2_per_page_page_1' => ['planner', null, 'unassigned', 2, 1, 3, 2],
            'demo_unassigned_2_per_page_page_2' => ['planner', null, 'unassigned', 2, 2, 3, 1],
            'demo_assigned_default_pagination' => ['planner', null, 'assigned', null, null, 8, 8],
            'demo_assigned_3_per_page_page_1' => ['planner', null, 'assigned', 3, 1, 8, 3],
            'demo_assigned_3_per_page_page_3' => ['planner', null, 'assigned', 3, 3, 8, 2],
            'demo_assigned_4_per_page_page_1' => ['planner', null, 'assigned', 4, 1, 8, 4],
            'demo_assigned_4_per_page_page_2' => ['planner', null, 'assigned', 4, 2, 8, 4],
            'demo_assigned_4_per_page_page_3' => ['planner', null, 'assigned', 4, 3, 8, 0],
            'demo_outsourced_default_pagination' => ['planner', null, 'outsourced', null, null, 12, 12],
            'demo_queued_default_pagination' => ['planner', null, 'queued', null, null, 3, 3],
            'demo_completed_default_pagination' => ['planner', null, 'completed', null, null, 1, 1],
            'demo_archived_default_pagination' => ['planner', null, 'archived', null, null, 1, 1],
            // demo organisation, case list
            'demo_caselist2_unassigned' => ['planner', 'caseList2', 'unassigned', null, null, 2, 2],
            'demo_caselist3_unassigned' => ['planner', 'caseList3', 'unassigned', null, null, 0, 0],
            'demo_caselist2_assigned' => ['planner', 'caseList2', 'assigned', null, null, 1, 1],
            'demo_caselist2_completed' => ['planner', 'caseList2', 'completed', null, null, 0, 0],
            // outsource organisation
            'outsource_unassigned' => ['outsourcePlanner', null, 'unassigned', null, null, 8, 8],
            'outsource_assigned' => ['outsourcePlanner', null, 'assigned', null, null, 5, 5],
            'outsource_outsourced' => ['outsourcePlanner', null, 'outsourced', null, null, 0, 0],
            'outsource_queued' => ['outsourcePlanner', null, 'queued', null, null, 2, 2],
            'outsource_completed' => ['outsourcePlanner', null, 'completed', null, null, 0, 0],
            // outsource organisation, case list
            'outsource_caselist2_unassigned' => ['outsourcePlanner', 'outsourceCaseList2', 'unassigned', null, null, 2, 2],
            'outsource_caselist3_unassigned' => ['outsourcePlanner', 'outsourceCaseList3', 'unassigned', null, null, 1, 1],
            'outsource_caselist2_assigned' => ['outsourcePlanner', 'outsourceCaseList2', 'assigned', null, null, 0, 0],
            'outsource_caselist2_completed' => ['outsourcePlanner', 'outsourceCaseList2', 'completed', null, null, 0, 0],
        ];
    }

    public function testFilterOnStatusIndexContactTracing(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->createCaseForOrganisation($organisation, [
            'status_index_contact_tracing' => ContactTracingStatus::looseEnd(),
            'bco_status' => BCOStatus::open(),
        ]);
        $this->createCaseForOrganisation($organisation, [
            'status_index_contact_tracing' => ContactTracingStatus::callbackRequest(),
            'bco_status' => BCOStatus::open(),
        ]);

        $query = [];
        $query['statusIndexContactTracing'] = ContactTracingStatus::looseEnd()->value;
        $uri = sprintf('/api/cases/unassigned/?%s', http_build_query($query));

        $response = $this->be($planner)->getJson($uri);

        $this->assertStatus($response, 200);
        $this->assertCount(1, $response->json('data'));
    }

    #[DataProvider('filterOnTestResultSourceProvider')]
    public function testFilterOnTestResultSource(
        array $testResultSources,
        int $expectedNumberOfCases,
    ): void {
        // Given an Organisation
        $organisation = $this->createRegionalOrganisation();
        // And a User with Role 'planner' in this organisation
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // And a Cases for that organisation
        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::open(),
        ]);
        // And this Case having the following TestResults
        foreach ($testResultSources as $testResultSource) {
            $this->createTestResultForCase($case, [
                'source' => $testResultSource,
            ]);
        }

        $query = [];
        // When the unassigned Cases are filtered on TestResultSource 'CoronIT'
        $query['testResultSource'] = TestResultSource::coronit()->value;
        $uri = sprintf('/api/cases/unassigned/?%s', http_build_query($query));
        // Then only a certain number of Cases should be returned
        $response = $this->be($planner)->getJson($uri);
        $response->assertOk();
        self::assertCount($expectedNumberOfCases, $response->json('data'));
    }

    public static function filterOnTestResultSourceProvider(): Generator
    {
        yield 'Multiple TestResults with one matching TestResultSource' => [
            [TestResultSource::coronit(), TestResultSource::meldportaal()],
            1,
        ];
        yield 'One TestResult with different TestResultSource' => [
            [TestResultSource::publicWebPortal()],
            0,
        ];
        yield 'No TestResults' => [[], 0];
    }

    #[DataProvider('filterOnAgeProvider')]
    public function testFilterOnAge(
        array $createCasesWithAge,
        int $queryMinAge,
        int $queryMaxAge,
        array $expectedAges,
    ): void {
        $organisation = $this->createRegionalOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        foreach ($createCasesWithAge as $age) {
            $this->createCaseForOrganisation($organisation, [
                'bco_status' => BCOStatus::open(),
                'index_age' => $age,
            ]);
        }
        $query = [
            'minAge' => $queryMinAge,
            'maxAge' => $queryMaxAge,
        ];
        $uri = sprintf('/api/cases/unassigned/?%s', http_build_query($query));
        $response = $this->be($planner)->getJson($uri);

        self::assertEqualsCanonicalizing(
            $expectedAges,
            collect($response->json('data'))->pluck('index_age')->toArray(),
        );
    }

    public static function filterOnAgeProvider(): Generator
    {
        yield 'All ages in range' => [[20, 30], 0, 120, [20, 30]];
        yield 'Some ages in range' => [[20, 30], 25, 120, [30]];
        yield 'No ages in range' => [[20, 30], 21, 29, []];
    }

    #[DataProvider('countsProvider')]
    #[Group('counts')]
    public function testCounts(
        string $userRole,
        ?string $caseList,
        int $expectedUnassigned,
        int $expectedAssigned,
        int $expectedOutsourced,
        ?int $expectedQueued,
        int $expectedCompleted,
        int $expectedArchived,
    ): void {
        // create entities for testdata
        $organisations = [];
        $organisations['organisation'] = $this->createOrganisation();
        $organisations['outsourceOrganisation'] = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $organisations['organisation']->outsourceOrganisations()->attach($organisations['outsourceOrganisation']);

        $users = [];
        $users['user'] = $this->createUserForOrganisation($organisations['organisation'], [], 'user');
        $users['planner'] = $this->createUserForOrganisation($organisations['organisation'], [], 'planner');
        $users['outsourceUser'] = $this->createUserForOrganisation($organisations['outsourceOrganisation'], [], 'user_nationwide');
        $users['outsourcePlanner'] = $this->createUserForOrganisation($organisations['outsourceOrganisation'], [], 'planner_nationwide');

        $caseLists = [];
        $caseLists['caseList1'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 1,
            'is_queue' => 1,
        ]);
        $caseLists['caseList2'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);
        $caseLists['caseList3'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);

        $caseLists['outsourceCaseList1'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 1,
            'is_queue' => 1,
        ]);
        $caseLists['outsourceCaseList2'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);
        $caseLists['outsourceCaseList3'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);

        $this->createTestCases($users, $organisations, $caseLists);

        $uriCaseList = $caseList ? sprintf('caselists/%s/', $caseLists[$caseList]->uuid) : '';
        $uri = sprintf('/api/%scases/counts', $uriCaseList);
        $response = $this->be($users[$userRole])->getJson($uri);

        $this->assertStatus($response, 200);
        $this->assertEquals($expectedUnassigned, $response->json('unassigned'));
        $this->assertEquals($expectedAssigned, $response->json('assigned'));
        $this->assertEquals($expectedOutsourced, $response->json('outsourced'));
        if ($expectedQueued === null) {
            $this->assertArrayNotHasKey('queued', $response->json());
        } else {
            $this->assertEquals($expectedQueued, $response->json('queued'));
        }
        $this->assertEquals($expectedCompleted, $response->json('completed'));
        $this->assertEquals($expectedArchived, $response->json('archived'));
    }

    public static function countsProvider(): array
    {
        return [
            'demo_all' => ['planner', null, 3, 8, 12, 3, 1, 1],
            'demo_caselist2' => ['planner', 'caseList2', 2, 1, 0, null, 0, 0],
            'demo_caselist3' => ['planner', 'caseList3', 0, 0, 0, null, 0, 0],
            'outsource_all' => ['outsourcePlanner', null, 8, 5, 0, 2, 0, 0],
            'outsource_caselist2' => ['outsourcePlanner', 'outsourceCaseList2', 2, 0, 0, null, 0, 0],
            'outsource_caselist3' => ['outsourcePlanner', 'outsourceCaseList3', 1, 0, 0, null, 0, 0],
        ];
    }

    #[DataProvider('sortAndOrderProvider')]
    public function testSortAndOrder(string $sort, string $order, string $expectedCaseId): void
    {
        // create entities for testdata
        $organisations = [];
        $organisations['organisation'] = $this->createOrganisation();
        $organisations['outsourceOrganisation'] = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $organisations['organisation']->outsourceOrganisations()->attach($organisations['outsourceOrganisation']);

        $users = [];
        $users['user'] = $this->createUserForOrganisation($organisations['organisation'], [], 'user');
        $users['planner'] = $this->createUserForOrganisation($organisations['organisation'], [], 'planner');
        $users['outsourceUser'] = $this->createUserForOrganisation($organisations['outsourceOrganisation'], [], 'user_nationwide');
        $users['outsourcePlanner'] = $this->createUserForOrganisation($organisations['outsourceOrganisation'], [], 'planner_nationwide');

        $caseLists = [];
        $caseLists['caseList1'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 1,
            'is_queue' => 1,
        ]);
        $caseLists['caseList2'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);
        $caseLists['caseList3'] = $this->createCaseListForOrganisation($organisations['organisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);

        $caseLists['outsourceCaseList1'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 1,
            'is_queue' => 1,
        ]);
        $caseLists['outsourceCaseList2'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);
        $caseLists['outsourceCaseList3'] = $this->createCaseListForOrganisation($organisations['outsourceOrganisation'], [
            'is_default' => 0,
            'is_queue' => 0,
        ]);

        $this->createTestCases($users, $organisations, $caseLists);

        $response = $this->be($users['planner'])->getJson(sprintf('/api/cases/unassigned/?sort=%s&order=%s', $sort, $order));
        $data = $response->json('data');
        $this->assertEquals($expectedCaseId, $data[0]['caseId']);
    }

    public static function sortAndOrderProvider(): array
    {
        return [
            'created_at_asc' => ['createdAt', 'asc', '1000031'],
            'created_at_desc' => ['createdAt', 'desc', '1000029'],
            'updated_at_asc' => ['updatedAt', 'asc', '1000031'],
            'updated_at_desc' => ['updatedAt', 'desc', '1000029'],
        ];
    }

    #[DataProvider('sortAndOrderPermittedProvider')]
    public function testSortAndOrderPermitted(PlannerView $view, PlannerSort $sort): void
    {
        $user = $this->createUser([], 'planner');
        $response = $this->be($user)->getJson(sprintf('/api/cases/%s/?sort=%s', $view, $sort));

        if ($view === PlannerView::completed() && $sort === PlannerSort::contactsCount()) {
            $response->assertStatus(422);
        } else {
            $response->assertSuccessful();
        }
    }

    public static function sortAndOrderPermittedProvider(): iterable
    {
        return TRegxDataProvider::cross(
            TRegxDataProvider::dictionary(PlannerView::all()),
            TRegxDataProvider::dictionary(PlannerSort::all()),
        );
    }

    public function testPlannerCaseListProperties(): void
    {
        $organisation1 = $this->createOrganisation();
        $organisation2 = $this->createOrganisation();
        $organisation3 = $this->createOrganisation();

        $planner = $this->createUserForOrganisation($organisation1, [], 'planner');

        $case = $this->createCaseForUser($planner, [
            'case_id' => $this->faker->unique()->uuid,
            'created_at' => CarbonImmutable::create(2020),
            'updated_at' => CarbonImmutable::create(2020),
            'bco_status' => BCOStatus::open(),
            'date_of_test' => null,
            'statusIndexContactTracing' => StatusIndexContactTracing::NEW(),
        ]);

        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation1, ['label' => 'foo'], ['sortorder' => 20]);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation1, ['label' => 'bar'], ['sortorder' => 10]);

        $organisation2->caseLabels()->save($caseLabel1);
        $organisation2->caseLabels()->save($caseLabel2);
        $organisation3->caseLabels()->save($caseLabel1);
        $organisation3->caseLabels()->save($caseLabel2);

        $case->caseLabels()->attach($caseLabel1->uuid);
        $case->caseLabels()->attach($caseLabel2->uuid);

        $response = $this->be($planner)->get('api/cases/assigned');

        $response->assertJson([
            'from' => 1,
            'to' => 1,
            'currentPage' => 1,
            'data' => [
                [
                    'uuid' => $case->uuid,
                    'caseId' => $case->case_id,
                    'contactsCount' => 0,
                    'dateOfBirth' => null,
                    'dateOfTest' => null,
                    'statusIndexContactTracing' => StatusIndexContactTracing::NEW()->value,
                    'statusExplanation' => '',
                    'createdAt' => '2020-01-01T00:00:00Z',
                    'updatedAt' => '2020-01-01T00:00:00Z',
                    'organisation' => [
                        'uuid' => $organisation1->uuid,
                        'abbreviation' => null,
                        'name' => $organisation1->name,
                        'isCurrent' => true,
                    ],
                    'assignedOrganisation' => null,
                    'assignedCaseList' => null,
                    'assignedUser' => [
                        'uuid' => $planner->uuid,
                        'isCurrent' => true,
                        'name' => $planner->name,
                    ],
                    'isEditable' => false,
                    'isDeletable' => false,
                    'isAssignable' => true,
                    'label' => null,
                    'plannerView' => 'assigned',
                    'wasOutsourced' => false,
                    'wasOutsourcedToOrganisation' => null,
                    'priority' => Priority::none()->value,
                    'caseLabels' => [
                        ['uuid' => $caseLabel1->uuid, 'label' => 'foo'],
                        ['uuid' => $caseLabel2->uuid, 'label' => 'bar'],
                    ],
                ],
            ],
        ]);
    }

    public function testPaginationReturnsUniqueResultsPerPage(): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // create 90 new cases with unique caseId's
        for ($i = 0; $i < 90; $i++) {
            $this->createCaseForUser($planner, [
                'case_id' => $this->faker->unique()->uuid,
                'updated_at' => CarbonImmutable::create(2020),
            ]);
        }

        $returnedCaseIds = collect();

        // retrieve first set, add caseId to collection
        $response = $this->be($planner)->get('api/cases/assigned?page=1&perPage=30');
        $returnedCaseIds = $returnedCaseIds->merge(collect($response->json()['data'])->pluck('caseId'));

        $response = $this->be($planner)->get('api/cases/assigned?page=2&perPage=30');
        $returnedCaseIds = $returnedCaseIds->merge(collect($response->json()['data'])->pluck('caseId'));

        $response = $this->be($planner)->get('api/cases/assigned?page=3&perPage=30');
        $returnedCaseIds = $returnedCaseIds->merge(collect($response->json()['data'])->pluck('caseId'));

        // total set should now have 3 x 30 = 90 unique identifiers
        $this->assertCount(90, $returnedCaseIds->unique());
    }

    public function testCreateCaseWithOrganisationLabel(): void
    {
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
                'bsn' => '123456789',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
                'notes' => 'a note',
            ],
            'label' => 'Owner org label',
        ];

        $response = $this->be($planner)->postJson('/api/cases', $caseData);

        $this->assertStatus($response, 201);
        $data = $response->json();
        $this->assertNotEmpty($data['data']['uuid']);

        $response = $this->getJson('/api/cases/planner/' . $data['data']['uuid']);
        $this->assertStatus($response, 200);
        $this->assertEquals('Owner org label', $response->json('data')['label']);
    }

    public function testCreateCaseAssignsToTheCurrentUser(): void
    {
        // Given a user and some case data
        $user = $this->createUser([], 'user', ['type' => OrganisationType::regionalGGD()]);

        // When the case is successfully created with a referer header
        $response = $this->be($user)->postJson('/api/cases', self::VALID_CASE_DATA);
        $this->assertStatus($response, 201);

        // Then the case should be assigned to the user
        $caseUuid = $response->json('data')['uuid'];
        $case = $this->getCase($caseUuid);
        $this->assertEquals($user->uuid, $case->assigned_user_uuid);
    }

    public function testCreateCaseDoesNotAssignedWhenCalledFromThePlannerScreen(): void
    {
        // Given a planner and some case data
        $planner = $this->createUser([], 'planner', ['type' => OrganisationType::regionalGGD()]);

        // When the case is successfully created with a referer header that indicates the planner screen
        $response = $this->be($planner)->postJson('/api/cases', self::VALID_CASE_DATA, ['Referer' => '/planner']);
        $this->assertStatus($response, 201);

        // Then the case should not be assigned
        $caseUuid = $response->json('data')['uuid'];
        $case = $this->getCase($caseUuid);
        $this->assertNull($case->assigned_user_uuid);
    }

    public function testUpdateLabelForAssignedOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        $outsourceOrganisation = $this->createOrganisation();
        $outsourcingPlanner = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');

        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);

        $case = $this->createCaseForOrganisation($planner->organisations->first(), [
            'created_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
            'organisation_label' => 'Owner org label',
            'bco_status' => BCOStatus::open(),
        ]);

        // update case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
                'bsn' => '123456789',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
                'notes' => 'a note',
            ],
            'label' => 'Assigned label',
        ];

        $response = $this->be($outsourcingPlanner)->putJson('/api/cases/planner/' . $case->uuid, $caseData);

        $this->assertStatus($response, 200);
        $data = $response->json();
        $this->assertEquals('Assigned label', $data['data']['label']);

        $response = $this->getJson('/api/cases/planner/' . $data['data']['uuid']);
        $this->assertStatus($response, 200);
        $this->assertEquals('Assigned label', $response->json('data')['label']);

        //Assign to organisation owner
        $case = EloquentCase::find($case->uuid);
        $case->assignedOrganisationUuid = null;
        $case->save();

        $response = $this->be($planner)->getJson('/api/cases/planner/' . $case->uuid);
        $this->assertStatus($response, 200);
        $this->assertEquals('Owner org label', $response->json('data')['label']);
    }

    public function testAssignedOrganisationLabelShouldBeResetOnReassignment(): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        $outsourceOrganisation = $this->createOrganisation();
        $outsourcePlanner = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');
        $outsourceUser = $this->createUserForOrganisation($outsourceOrganisation, [
            'last_login_at' => CarbonImmutable::now()->subDay(1),
        ], 'user');

        $case = $this->createCaseForUser($planner, [
            'created_at' => CarbonImmutable::now(),
            'organisation_uuid' => $organisation->uuid,
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
            'assigned_organisation_label' => 'Assigned label',
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($outsourcePlanner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $outsourceUser->uuid,
        ]);
        $response->assertStatus(200);

        $case->refresh();
        $this->assertEquals('Assigned label', $case->assigned_organisation_label);

        $response = $this->be($outsourcePlanner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => null, //ReturnToOwner
        ]);
        $response->assertStatus(200);

        $case->refresh();
        $this->assertNull($case->assigned_organisation_label);
    }

    #[DataProvider('searchCaseByDataProvider')]
    public function testSearchCaseBy(string $searchTerm): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForUser($planner, [
            'created_at' => CarbonImmutable::now(),
            'case_id' => '123-123-123',
            'bco_status' => BCOStatus::open(),
        ]);
        $case->general->hpzoneNumber = '1231231';
        $case->test->monsterNumber = '123A0987';
        $case->save();

        $response = $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $searchTerm,
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($case->uuid, $data['uuid']);

        $this->assertEquals(PlannerView::assigned()->value, $data['plannerView']);
    }

    public static function searchCaseByDataProvider(): array
    {
        return [
            'by case_id' => ['123-123-123'],
            'by hpzone_number' => ['1231231'],
            'by monster_number' => ['123A0987'],
        ];
    }

    public function testSearchCaseByHPZoneIdAssignedToOutsourcedOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $otherOrganisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => CarbonImmutable::now(),
            'hpzone_number' => '1234567',
            'assigned_organisation_uuid' => $otherOrganisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $case->caseId,
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($case->uuid, $data['uuid']);

        $this->assertEquals(PlannerView::outsourced()->value, $data['plannerView']);
    }

    public function testSearchCaseByHPZoneIdQueued(): void
    {
        $caseList = $this->createCaseList([
            'is_queue' => true,
        ]);
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => CarbonImmutable::now(),
            'hpzone_number' => '1234567',
            'assigned_case_list_uuid' => $caseList->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $case->caseId,
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($case->uuid, $data['uuid']);

        $this->assertEquals(PlannerView::queued()->value, $data['plannerView']);
    }

    public function testSearchCaseByHPZoneIdCompleted(): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => CarbonImmutable::now(),
            'hpzone_number' => '1234567',
            'bco_status' => BCOStatus::completed(),
        ]);

        $response = $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $case->caseId,
        ]);
        $response->assertStatus(200);

        $data = $response->json();
        $this->assertEquals($case->uuid, $data['uuid']);

        $this->assertEquals(PlannerView::completed()->value, $data['plannerView']);
    }

    public function testSearchCaseForOtherOrganisationByHPZoneIdShouldNotBeFound(): void
    {
        $otherOrganisation = $this->createOrganisation();
        $planner = $this->createUser([], 'planner');

        $case = $this->createCaseForUser($planner, [
            'created_at' => CarbonImmutable::now(),
            'organisation_uuid' => $otherOrganisation->uuid,
            'case_id' => '1234567',
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->postJson('/api/cases/planner/search', [
            'identifier' => $case->caseId,
        ]);
        $response->assertStatus(404);
    }

    #[Group('planner-case-was-outsourced')]
    public function testBasicListForCasesContainsWasOutsourcedProperties(): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, ['last_login_at' => CarbonImmutable::now()], 'user,planner');

        $outsourceOrganisation = $this->createOrganisation([
            'isAvailableForOutsourcing' => true,
        ]);
        $outsourcePlanner = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');

        $organisation->outsourceOrganisations()->attach($outsourceOrganisation);
        $organisation->save();

        $case = $this->createCase([
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->getJson('/api/cases/unassigned?includeTotal=1');
        $this->assertStatus($response, 200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertCount(1, $response->json('data'));
        $this->assertFalse($response->json('data.0.wasOutsourced'));
        $this->assertEquals(null, $response->json('data.0.wasOutsourcedToOrganisation'));

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => $outsourceOrganisation->uuid,
        ]);
        $response->assertStatus(200);

        sleep(1); // need to sleep for a little to get the ordering consistent

        // return to owner, planner of owner organisation should see the previously assigned outsource organisation name
        $response = $this->be($outsourcePlanner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedOrganisationUuid' => null, // return to owner
        ]);
        $response->assertStatus(200);
        $response = $this->be($planner)->getJson('/api/cases/unassigned?includeTotal=1');
        $this->assertStatus($response, 200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertCount(1, $response->json('data'));
        $this->assertTrue($response->json('data.0.wasOutsourced'));
        $this->assertEquals($outsourceOrganisation->name, $response->json('data.0.wasOutsourcedToOrganisation.name'));

        sleep(1);

        // assign to a user of the owner organisation and return, that the case was once outsourced should not be visible anymore
        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => $planner->uuid,
        ]);
        $response->assertStatus(200);

        sleep(1);

        $response = $this->be($planner)->putJson('/api/cases/' . $case->uuid . '/assignment', [
            'assignedUserUuid' => null,
        ]);
        $response->assertStatus(200);
        $response = $this->be($planner)->getJson('/api/cases/unassigned?includeTotal=1');
        $this->assertStatus($response, 200);
        $this->assertEquals(1, $response->json('total'));
        $this->assertCount(1, $response->json('data'));
        $this->assertFalse($response->json('data.0.wasOutsourced'));
        $this->assertEquals(null, $response->json('data.0.wasOutsourcedToOrganisation'));
    }

    #[DataProvider('validPlannerCaseDataProvider')]
    public function testCreatePlannerCase(array $payload, array $expectedCovidCaseData): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(201);

        $this->assertDatabaseHas('covidcase', $expectedCovidCaseData);
    }

    public function testCreateCaseWithReferenceIsProhibited(): void
    {
        $payload = self::VALID_CASE_DATA;
        $payload['general']['reference'] = $this->faker->numberBetween(1_000_000, 99_999_999);

        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(422);
        $this->assertEquals(
            'The referentie field is prohibited.',
            $response->json()['validationResult']['fatal']['errors']['general.reference'][0],
        );
    }

    #[DataProvider('invalidPlannerCaseDataProvider')]
    public function testCreatePlannerCaseValidation(array $payload): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(422);
    }

    public function testCreatePlannerCaseWithCaseLabels(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation);

        $payload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
            'caseLabels' => [
                $caseLabel1->uuid,
                $caseLabel2->uuid,
            ],
        ];
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(201);

        $caseUuid = $response->json('data.uuid');

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $caseUuid,
            'hpzone_number' => '1234567',
        ]);
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $caseUuid,
            'case_label_uuid' => $caseLabel1->uuid,
        ]);
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $caseUuid,
            'case_label_uuid' => $caseLabel2->uuid,
        ]);
    }

    public function testCreatePlannerCaseWithCaseLabelFromOtherOrganisation(): void
    {
        $organisation1 = $this->createRegionalOrganisation();
        $organisation2 = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation1, [], 'planner');
        $caseLabel = $this->createCaseLabelForOrganisation($organisation2);

        $payload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
            'caseLabels' => [
                $caseLabel->uuid,
            ],
        ];
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'caseLabels.0' => 'no permission to edit entity for caseLabels.0',
        ]);
    }

    #[DataProvider('validPlannerCaseDataProvider')]
    public function testUpdatePlannerCase(array $payload, array $expectedCovidCaseData): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::draft(),
                'date_of_test' => null,
            ],
        );

        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s', $case->uuid), $payload);

        $response->assertStatus(200);

        $this->assertDatabaseHas('covidcase', $expectedCovidCaseData);
    }

    public function testUpdatePlannerCaseWithCaseLabels(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::draft(),
                'date_of_test' => null,
            ],
        );

        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'foo']);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'bar']);
        $caseLabel3 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'baz']);

        // attach label1 and label2 to case
        $case->caseLabels()->attach($caseLabel1->uuid);
        $case->caseLabels()->attach($caseLabel2->uuid);

        // send label1 and label3 on update-request
        $payload = [
            'caseLabels' => [
                $caseLabel1->uuid,
                $caseLabel3->uuid,
            ],
        ];

        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s', $case->uuid), $payload);
        $response->assertStatus(200);

        // validate label1 and label3 are attached
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $case->uuid,
            'case_label_uuid' => $caseLabel1->uuid,
        ]);
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $case->uuid,
            'case_label_uuid' => $caseLabel3->uuid,
        ]);

        // validate label2 is no longer attached
        $this->assertDatabaseMissing('case_case_label', [
            'case_uuid' => $case->uuid,
            'case_label_uuid' => $caseLabel2->uuid,
        ]);
    }

    public function testUpdatePlannerCaseMeta(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::draft(),
            ],
        );

        $caseLabel = $this->createCaseLabelForOrganisation($organisation, ['label' => $this->faker->word()]);
        $priority = $this->faker->randomElement(Priority::allValues());
        $payload = [
            'priority' => $priority,
            'caseLabels' => [
                $caseLabel->uuid,
            ],
        ];

        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s/meta', $case->uuid), $payload);
        $response->assertStatus(200);

        $this->assertDatabaseHas('covidcase', ['priority' => $priority]);
        $this->assertDatabaseHas('case_case_label', [
            'case_uuid' => $case->uuid,
            'case_label_uuid' => $caseLabel->uuid,
        ]);
    }

    public function testUpdatePlannerCaseMetaWhenNoPermission(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case = $this->createCaseForOrganisation(
            $organisation,
            [
                'bco_status' => BCOStatus::archived(),
            ],
        );

        $payload = [
            'priority' => $this->faker->randomElement(Priority::allValues()),
        ];

        $response = $this->be($user)->putJson(sprintf('/api/cases/planner/%s/meta', $case->uuid), $payload);
        $response->assertStatus(403);
    }

    public function testGetPlannerCase(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
            'bco_phase' => BCOPhase::phase1(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $caseLabel1 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'foo'], ['sortorder' => 10]);
        $caseLabel2 = $this->createCaseLabelForOrganisation($organisation, ['label' => 'bar'], ['sortorder' => 20]);

        $index = Index::newInstanceWithVersion(1);
        $index->firstname = 'foo';
        $index->lastname = 'bar';
        $index->initials = 'fb';
        $general = General::newInstanceWithVersion(1);
        $general->reference = 'AA1-123-123';
        $general->hpzoneNumber = '1234567';
        $contact = Contact::newInstanceWithVersion(1);
        $contact->phone = '06 12345678';

        $case = $this->createCaseForOrganisation($organisation, [
            'general' => $general,
            'index' => $index,
            'contact' => $contact,
            'priority' => Priority::high(),
            'created_at' => new CarbonImmutable('yesterday'),
            'bco_status' => BCOStatus::draft(),
            'date_of_test' => null,
            'test_monster_number' => '123A4567',
        ]);
        $case->caseLabels()->attach($caseLabel1->uuid);
        $case->caseLabels()->attach($caseLabel2->uuid);

        $response = $this->be($user)->getJson(sprintf('/api/cases/planner/%s', $case->uuid));
        $this->assertSame(200, $response->getStatusCode());

        $response->assertExactJson([
            'data' => [
                'uuid' => $case->uuid,
                'priority' => $case->priority->value,
                'general' => [
                    'reference' => $case->case_id,
                    'hpzoneNumber' => $case->hpzone_number,
                    'organisation' => [
                        'uuid' => $organisation->uuid,
                        'abbreviation' => $organisation->abbreviation,
                        'externalId' => $organisation->external_id,
                        'hpZoneCode' => $organisation->hp_zone_code,
                        'name' => $organisation->name,
                        'phoneNumber' => $organisation->phone_number,
                        'bcoPhase' => $organisation->bco_phase->value,
                    ],
                    'notes' => $general->notes,
                ],
                'index' => [
                    'firstname' => $index->firstname,
                    'initials' => $index->initials,
                    'lastname' => $index->lastname,
                    'dateOfBirth' => $index->dateOfBirth,
                    'address' => $index->address,
                    'bsnCensored' => $index->bsnCensored,
                    'bsnLetters' => $index->bsnLetters,
                ],
                'contact' => [
                    'email' => $contact->email,
                    'phone' => $contact->phone,
                ],
                'test' => [
                    'monsterNumber' => '123A4567',
                    'dateOfTest' => null,
                ],
                'automaticAddressVerificationStatus' => $case->automatic_address_verification_status->value,
                'caseLabels' => [
                    [
                        'is_selectable' => true,
                        'uuid' => $caseLabel2->uuid,
                        'label' => $caseLabel2->label,
                    ],
                    [
                        'is_selectable' => true,
                        'uuid' => $caseLabel1->uuid,
                        'label' => $caseLabel1->label,
                    ],
                ],
            ],
        ]);
    }

    public function testGetPlannerCaseRunsValidationsToEnableUserFeedback(): void
    {
        $organisation = $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
            'bco_phase' => BCOPhase::phase1(),
        ]);
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $index = Index::newInstanceWithVersion(1);
        $index->firstname = $this->faker->firstName();
        $index->lastname = $this->faker->lastName();
        $index->initials = $this->faker->randomLetter();

        $address = IndexAddress::newInstanceWithVersion(1);
        $address->postalCode = 'invalid';
        $address->houseNumber = 'invalid';

        $index->address = $address;

        $contact = Contact::newInstanceWithVersion(1);
        $contact->phone = $this->faker->phoneNumber;

        $case = $this->createCaseForOrganisation($organisation, [
            'index' => $index,
            'contact' => $contact,
            'priority' => Priority::high(),
            'created_at' => $this->faker->dateTimeThisDecade,
            'bco_status' => BCOStatus::draft(),
        ]);

        $response = $this->be($user)->getJson(sprintf('/api/cases/planner/%s', $case->uuid));
        $this->assertSame(200, $response->getStatusCode());
        $response->assertJsonPath(
            ['validationResult', 'warning', 'errors', 'index.address.postalCode', 0],
            'Dit is geen geldige postcode.',
        );
        $response->assertJsonPath(
            ['validationResult', 'warning', 'errors', 'index.address.houseNumber', 0],
            'Veld "Huisnummer" moet een nummer zijn.',
        );
    }

    public static function validPlannerCaseDataProvider(): array
    {
        $minimalValidPayload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ];

        $expectedCovidCaseData = [
            'hpzone_number' => '1234567',
            'priority' => 'none',
        ];

        return [
            'minimal' => [$minimalValidPayload, $expectedCovidCaseData],
            'with priority' => [
                array_merge($minimalValidPayload, ['priority' => '2']),
                array_merge($expectedCovidCaseData, ['priority' => '2']),
            ],
            'with test.monster_number' => [
                array_merge($minimalValidPayload, ['test' => ['monsterNumber' => '123A123']]),
                array_merge($expectedCovidCaseData, ['test_monster_number' => '123A123']),
            ],
            'with max length test.monster_number' => [
                array_merge($minimalValidPayload, ['test' => ['monsterNumber' => '123A123456789012']]),
                array_merge($expectedCovidCaseData, ['test_monster_number' => '123A123456789012']),
            ],
        ];
    }

    public static function invalidPlannerCaseDataProvider(): array
    {
        $validPayload = collect([
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'reference' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ]);

        return [
            'without index.firstname' => [$validPayload->except('index.firstname')->toArray()],
            'without index.lastname' => [$validPayload->except('index.lastname')->toArray()],
            'without contact. phone' => [$validPayload->except('contact.phone')->toArray()],
            'invalid test.monsterNumber A' => [$validPayload->put('test', ['monsterNumber' => 'A'])->toArray()],
            'invalid test.monsterNumber 123' => [$validPayload->put('test', ['monsterNumber' => '123'])->toArray()],
            'invalid test.monsterNumber A123' => [$validPayload->put('test', ['monsterNumber' => 'A123'])->toArray()],
            'invalid test.monsterNumber 123A' => [$validPayload->put('test', ['monsterNumber' => '123A'])->toArray()],
            'invalid test.monsterNumber 1234A' => [
                $validPayload->put('test', ['monsterNumber' => '1234A'])->toArray(),
            ],
            'invalid test.monsterNumber 1234A1' => [
                $validPayload->put('test', ['monsterNumber' => '1234A1'])->toArray(),
            ],
            'test.monsterNumber too long' => [
                $validPayload->put('test', ['monsterNumber' => '123A1234567890123'])->toArray(),
            ],

            // not a required field, but if the test data is null (no values), the encoder fails
            // comment it out for now, created issue DBCO-2920
            // 'without test . dateOfTest' => [$validPayload->except('test . dateOfTest')->toArray()],
        ];
    }

    public function testBulkUpdatePlannerCasePriority(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case1 = $this->createCaseForOrganisation($organisation, [
            'priority' => Priority::normal(),
        ]);
        $case2 = $this->createCaseForOrganisation($organisation, [
            'priority' => Priority::veryHigh(),
        ]);

        $response = $this->be($user)->putJson('api/cases/priority', [
            'cases' => [
                $case1->uuid,
                $case2->uuid,
            ],
            'priority' => 2,
        ]);

        $response->assertStatus(204);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case1->uuid,
            'priority' => 2,
        ]);
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case2->uuid,
            'priority' => 2,
        ]);
    }

    public function testBulkUpdatePlannerCasePriorityWithUnauthorizedCase(): void
    {
        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $case1 = $this->createCaseForOrganisation($organisation, [
            'priority' => Priority::normal(),
        ]);
        $case2 = $this->createCase([
            'priority' => Priority::veryHigh(),
        ]);

        $response = $this->be($user)->putJson('api/cases/priority', [
            'cases' => [
                $case1->uuid,
                $case2->uuid,
            ],
            'priority' => 2,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'cases.1' => 'Geen toegang tot case',
        ]);
    }

    #[Group('planner-case-total')]
    public function testPlannerCaseListIncludeTotal(): void
    {
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, ['last_login_at' => CarbonImmutable::now()], 'user,planner');

        $this->createCase([
            'organisation_uuid' => $organisation->uuid,
            'bco_status' => BCOStatus::open(),
        ]);

        $response = $this->be($planner)->getJson('/api/cases/unassigned?includeTotal=1');
        $data = $response->json();
        $this->assertStatus($response, 200);
        $this->assertTrue(isset($data['currentPage']));
        $this->assertEquals(1, $data['currentPage']);
        $this->assertTrue(isset($data['total']));
        $this->assertEquals(1, $data['total']);
        $this->assertTrue(isset($data['lastPage']));
        $this->assertEquals(1, $data['lastPage']);

        $response = $this->be($planner)->getJson('/api/cases/unassigned?includeTotal=0');
        $data = $response->json();
        $this->assertStatus($response, 200);
        $this->assertTrue(isset($data['currentPage']));
        $this->assertEquals(1, $data['currentPage']);
        $this->assertFalse(isset($data['total']));
        $this->assertFalse(isset($data['lastPage']));

        $response = $this->be($planner)->getJson('/api/cases/unassigned');
        $data = $response->json();
        $this->assertStatus($response, 200);
        $this->assertTrue(isset($data['currentPage']));
        $this->assertEquals(1, $data['currentPage']);
        $this->assertFalse(isset($data['total']));
        $this->assertFalse(isset($data['lastPage']));
    }

    public function testNoteIsCreatedOnNewCase(): void
    {
        $planner = $this->createUser([], 'planner', ['abbreviation' => 'GGD1', 'type' => OrganisationType::regionalGGD()]);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
                'bsn' => '123456789',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
            ],
            'general' => [
                'hpzoneNumber' => '1234567',
                'notes' => 'a note',
            ],
            'notes' => 'the real note',
        ];

        $response = $this->be($planner)->postJson('/api/cases', $caseData);

        $this->assertStatus($response, 201);
        $data = $response->json();
        $this->assertNotEmpty($data['data']['uuid']);

        /** @var EloquentCase $case */
        $case = EloquentCase::find($data['data']['uuid']);

        /** @var CaseNoteService $noteService */
        $noteService = app(CaseNoteService::class);
        $note = $noteService->getNotes($case->uuid)->sole();
        $this->assertEquals('the real note', $note->note);
    }

    #[DataProvider('uniqueCaseMonsterNumberDataProvider')]
    public function testCreateCaseUniqueMonsterNumber(
        string $monsterNumber,
        int $expectedStatusCode,
    ): void {
        $planner = $this->createUser([], 'planner');

        $this->createCase([
            'test_monster_number' => '123A456',
        ]);

        // create case
        $caseData = [
            'index' => [
                'firstname' => 'John',
                'lastname' => 'Doe',
                'dateOfBirth' => '1950-01-01',
                'bsn' => '123456789',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'test' => [
                'dateOfTest' => CarbonImmutable::yesterday()->format('Y-m-d'),
                'monsterNumber' => $monsterNumber,
            ],
            'general' => [
                'notes' => 'a note',
            ],
        ];

        $response = $this->be($planner)->postJson('/api/cases', $caseData);

        $this->assertStatus($response, $expectedStatusCode);
    }

    public static function uniqueCaseMonsterNumberDataProvider(): array
    {
        return [
            'unique' => ['456B789', 201],
            'non-unique' => ['123A456', 422],
        ];
    }

    public function testCaseClosableWhenDraftAndUnassigned(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // AND a Case is unassigned and in draft Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::draft(),
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/unassigned/');
        // THEN the Case is closable
        $this->assertTrue($response->json('data.0.isClosable'));
    }

    public function testCaseIsClosableWhenCompletedAndUnassigned(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // AND a Case is unassigned and in completed Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::completed(),
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/completed/');
        // THEN the Case is closable
        $this->assertTrue($response->json('data.0.isClosable'));
    }

    public function testCaseClosableWhenCompletedAndAssignedToUser(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // AND a Case is assigned and in completed Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::completed(),
            'assigned_user_uuid' => $planner->uuid,
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/assigned/');
        // THEN the Case is closable
        $this->assertTrue($response->json('data.0.isClosable'));
    }

    public function testCaseNotClosableWhenArchivedAndOutsourced(): void
    {
        // GIVEN an Organisation has a Planner and an Outsource Organisation exists
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        // AND a Case is outsourced and in archived Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::archived(),
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/outsourced/');
        // THEN the Case is not closable
        $this->assertFalse($response->json('data.0.isClosable'));
    }

    public function testCaseClosableWhenOpenAndOutsourcedToCurrentOrganisation(): void
    {
        // GIVEN an Organisation has a Planner and an Outsource Organisation exists
        $organisation = $this->createOrganisation();
        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $outsourcePlanner = $this->createUserForOrganisation($outsourceOrganisation, [], 'planner');

        // AND a Case is outsourced and in archived Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::open(),
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);

        // WHEN the Case is queried on the list
        $response = $this->be($outsourcePlanner)->getJson('/api/cases/unassigned');
        // THEN the Case is not closable
        $this->assertTrue($response->json('data.0.isClosable'));
    }

    public function testCaseNotClosableWhenDraftAndOutsourced(): void
    {
        // GIVEN an Organisation has a Planner and an Outsource Organisation exists
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        // AND a Case is outsourced and in draft Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::draft(),
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/outsourced/');
        // THEN the Case is not closable
        $this->assertFalse($response->json('data.0.isClosable'));
    }

    public function testCaseAssignableWhenNotOutsourced(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // AND a Case is unassigned and in draft Status
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::draft(),
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/unassigned/');
        // THEN the Case is assignable
        $this->assertTrue($response->json('data.0.isAssignable'));
    }

    public function testCaseAssignableWhenOutsourcedAndNotAssignedToUser(): void
    {
        // GIVEN an Organisation has a Planner and an Outsource Organisation exists
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        // AND a Case is outsourced
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/outsourced/');
        // THEN the Case is assignable
        $this->assertTrue($response->json('data.0.isAssignable'));
    }

    public function testCaseNotAssignableWhenOutsourcedToOtherAndAssignedToUser(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // AND an Outsource Organisation exists which has a user
        $outsourceOrganisation = $this->createOrganisation([
            'type' => OrganisationType::outsourceOrganisation(),
        ]);
        $outsourceUser = $this->createUserForOrganisation($outsourceOrganisation, [], 'user');
        // AND a Case is outsourced and assigned to user
        $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'assigned_organisation_uuid' => $outsourceOrganisation->uuid,
            'assigned_user_uuid' => $outsourceUser->uuid,
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/outsourced/');
        // THEN the Case is assignable
        $this->assertFalse($response->json('data.0.isAssignable'));
    }

    public function testCaseAssignableWhenOutsourcedToMyOrganisationAndAssignedToUser(): void
    {
        // GIVEN an Organisation A has a Planner and a User
        $organisationA = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisationA, [], 'planner');
        $user = $this->createUserForOrganisation($organisationA, [], 'user');
        // AND another Organisation exists
        $organisationB = $this->createOrganisation();
        // AND a Case is outsourced by organisation B to organisation A
        $this->createCaseForOrganisation($organisationB, [
            'created_at' => new CarbonImmutable(),
            'assigned_organisation_uuid' => $organisationA->uuid,
            'assigned_user_uuid' => $user->uuid,
        ]);
        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/assigned/');
        // THEN the Case is assignable
        $this->assertTrue($response->json('data.0.isAssignable'));
    }

    public function testCaseReopenableWhenCompletedAndNotYetApproved(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');
        // AND a Case is completed and not yet approved
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::completed(),
            'isApproved' => null,
        ]);

        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/completed/');
        $reopenResponse = $this->patch(sprintf('api/cases/%s/reopen', $case->uuid), ['note' => $this->faker->word()]);

        // THEN Should be in view and it should be reopenable
        $this->assertTrue($response->json('data.0.isReopenable'));
        $reopenResponse->assertSuccessful();
    }

    public function testCaseNotReopenableWhenDraft(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // AND a Case is draft
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::draft(),
        ]);

        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/unassigned/');
        $reopenResponse = $this->patch(sprintf('api/cases/%s/reopen', $case->uuid), ['note' => $this->faker->word()]);

        // THEN Should not be in view and it should not be reopenable
        $this->assertFalse($response->json('data.0.isReopenable'));
        $reopenResponse->assertForbidden();
    }

    public function testCaseReopenableWhenCompletedAndDeclined(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // AND a Case is completed and Declined
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::completed(),
            'isApproved' => false,
        ]);

        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/unassigned/');
        $reopenResponse = $this->patch(sprintf('api/cases/%s/reopen', $case->uuid), ['note' => $this->faker->word()]);

        // THEN Should not be in view and it should not be reopenable
        $this->assertTrue($response->json('data.0.isReopenable'));
        $reopenResponse->assertSuccessful();
    }

    public function testCaseReopenableWhenArchivedAndYoungerThenRecentDays(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // AND a Case is archived within the recent days range
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::archived(),
            'updated_at' => CarbonImmutable::now(),
        ]);

        // WHEN the Case is queried on the list
        $response = $this->be($planner)->getJson('/api/cases/archived/');
        $reopenResponse = $this->patch(sprintf('api/cases/%s/reopen', $case->uuid), ['note' => $this->faker->word()]);

        // THEN Should be in view and it should be reopenable
        $this->assertTrue($response->json('data.0.isReopenable'));
        $reopenResponse->assertSuccessful();
    }

    public function testCaseReopenableWhenArchivedAndOlderThenRecentDays(): void
    {
        // GIVEN an Organisation has a Planner
        $organisation = $this->createOrganisation();
        $planner = $this->createUserForOrganisation($organisation, [], 'planner');

        // AND a Case is archived and not within the recent days range
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => new CarbonImmutable(),
            'bcoStatus' => BCOStatus::archived(),
            'updated_at' => CarbonImmutable::now()->subDays((int) $this->config->get('misc.planner.case_recent_days') + 1),
        ]);

        // WHEN the Case is queried on the list
        $this->be($planner);
        $archivedCases = $this->getJson('/api/cases/archived/');
        $reopenResponse = $this->patch(sprintf('api/cases/%s/reopen', $case->uuid), ['note' => $this->faker->word()]);

        // THEN Should not be in view and it should be reopenable
        $this->assertNull($archivedCases->json('data.0.isReopenable'));
        $this->assertFalse($case->isClosable());
        $reopenResponse->assertSuccessful();
    }

    public function testCreatePlannerCaseFailShouldRollback(): void
    {
        CarbonImmutable::setTestNow('2020-01-01');

        $this->mock(CaseFragmentService::class, static function (MockInterface $mock): void {
            $mock->expects('storeFragments')->andThrow(new Exception('Test exception'));
        });

        $organisation = $this->createRegionalOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');

        $payload = [
            'index' => [
                'firstname' => 'foo',
                'lastname' => 'bar',
                'dateOfBirth' => '1950-01-01',
            ],
            'contact' => [
                'phone' => '06 12345678',
            ],
            'general' => [
                'hpzone_number' => '1234567',
            ],
            'test' => [
                'dateOfTest' => null,
            ],
        ];
        $response = $this->be($user)->postJson('/api/cases', $payload);

        $response->assertStatus(500);

        $this->assertDatabaseMissing('covidcase', [
            'hpzone_number' => '1234567',
        ]);
    }

    #[DataProvider('createCaseWithDifferentCaseSchemaVersionsData')]
    #[Group('override-case-version')]
    public function testCreateCaseWithDifferentCaseSchemaVersions(int $version, string $fragment): void
    {
        // We need to clean the schema cache because it can already hold a schema with a specific version:
        SchemaCache::clear();

        try {
            $this->be($this->createUser());

            $this->config->set('schema.overrideCaseVersion', (string) $version);

            $caseUuid = $this
                ->postJson('/api/cases', self::VALID_CASE_DATA)
                ->assertStatus(201)
                ->json('data.uuid');

            $case = $this->getCase($caseUuid);

            $currentEloquentCaseSchemaVersion = EloquentCase::getSchema()->getCurrentVersion();

            $this->assertSame($version, $case->getSchemaVersion()->getVersion());
            $this->assertSame(
                $expected = $this->getFieldVersion($currentEloquentCaseSchemaVersion, $fragment),
                $actual = $case->{$fragment}->getSchemaVersion()->getVersion(),
                sprintf('Expected "%s" field to be v%s instead of v%s', $fragment, $expected, $actual),
            );
        } finally {
            SchemaCache::clear();
        }
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testItDispatchesAnEventWhenTheCaseIsChangedFromOrganisation(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        $initialOrganisation = $this->createOrganisation();
        $targetOrganisation = $this->createOrganisation();

        $case = $this->createCaseForOrganisation($initialOrganisation, [
            'bco_status' => BcoStatus::archived(),
        ]);

        $user = $this->createUserForOrganisation($initialOrganisation, [], 'planner');

        Queue::fake();

        $this->be($user)
            ->post(sprintf('/api/case/%s/update-organisation', $case->uuid), [
                'organisation_uuid' => $targetOrganisation->uuid,
            ]);

        Queue::assertPushed(
            ExportCaseToOsiris::class,
            static fn (ExportCaseToOsiris $job) => $job->caseUuid === $case->uuid && $job->caseExportType === CaseExportType::DEFINITIVE_ANSWERS,
        );
    }

    protected function createRegionalOrganisation(): EloquentOrganisation
    {
        return $this->createOrganisation([
            'type' => OrganisationType::regionalGGD(),
        ]);
    }

    public static function createCaseWithDifferentCaseSchemaVersionsData(): iterable
    {
        return TRegxDataProvider::cross(
            SchemaVersionDataProvider::all(EloquentCase::class),
            [
                'index' => ['index'],
                'contact' => ['contact'],
                'test' => ['test'],
                'general' => ['general'],
            ],
        );
    }

    /**
     * @template T of EloquentCase&SchemaObject
     *
     * @param SchemaVersion<T> $schemaVersion
     */
    private function getFieldVersion(SchemaVersion $schemaVersion, string $field): int
    {
        return $schemaVersion
            ->getExpectedField($field)
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();
    }

    private function getCase(string $caseUuid): ?EloquentCase
    {
        /** @var DbCaseRepository $dbCaseRepository */
        $dbCaseRepository = $this->app->get(DbCaseRepository::class);

        return $dbCaseRepository->getCase($caseUuid);
    }
}
