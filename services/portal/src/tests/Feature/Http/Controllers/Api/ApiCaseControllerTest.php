<?php

declare(strict_types=1);

namespace Tests\Feature\Http\Controllers\Api;

use App\Events\Osiris\CaseValidationRaisesNotice;
use App\Events\Osiris\CaseValidationRaisesWarning;
use App\Http\Requests\Api\CovidCase\UpdateContactStatusRequest;
use App\Jobs\ExportCaseToOsiris;
use App\Jobs\UpdatePlaceCounters;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentUser;
use App\Models\Enums\Osiris\CaseExportType;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Schema\Types\SchemaType;
use App\Services\Assignment\AssignmentTokenService;
use App\Services\Assignment\Enum\AssignmentModelEnum;
use App\Services\Assignment\TokenResource;
use App\Services\Osiris\SoapMessage\SoapMessageBuilder;
use Carbon\CarbonImmutable;
use Generator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Illuminate\Testing\Fluent\AssertableJson;
use MinVWS\DBCO\Enum\Models\BCOPhase;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use MinVWS\DBCO\Enum\Models\CasequalityFeedback;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Ramsey\Uuid\Uuid;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function array_map;
use function array_merge;
use function sprintf;

#[Group('case')]
class ApiCaseControllerTest extends FeatureTestCase
{
    public function testGetCaseWithoutAutheticationShouldReturnForbidden(): void
    {
        $this->getJson('/api/case/' . $this->faker->uuid())->assertUnauthorized();
    }

    public function testGetMyCasesShouldNotFilterCases(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $this->createCases($user, BCOStatus::all());

        $response = $this->getJson('/api/cases/mine');
        $response->assertStatus(200);

        $myCases = $response->json('cases')['data'];

        foreach ($myCases as $myCase) {
            $this->assertContainsEquals($myCase['bcoStatus'], BCOStatus::all());
        }
    }

    public function testGetMyCasesWithoutSpecificStatusShouldReturnResults(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $this->createCases($user, [BCOStatus::draft(), BCOStatus::open()]);

        $response = $this->getJson('/api/cases/mine');
        $response->assertStatus(200);

        $myCases = $response->json('cases')['data'];
        $this->assertCount(2, $myCases);
    }

    public function testGetMyCasesWithSpecificStatus(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $this->createCases($user, [BCOStatus::draft(), BCOStatus::open()]);

        $response = $this->getJson('/api/cases/mine/' . BCOStatus::open()->value);
        $response->assertStatus(200);

        $myCases = $response->json('cases')['data'];
        $this->assertCount(1, $myCases);
        $this->assertEquals(BCOStatus::open()->value, $myCases[0]['bcoStatus']);
    }

    public function testGetMyCasesWithCompleteStatusShouldFail(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $response = $this->getJson('/api/cases/mine/' . BCOStatus::completed()->value);
        $response->assertStatus(404);
    }

    public function testCreateCaseAsPlannerShouldWork(): void
    {
        $user = $this->createUser([], 'planner');
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->assertTrue(Uuid::isValid($case->uuid));
    }

    public function testPlannerCannotAccessCreatedCase(): void
    {
        $user = $this->createUser([], 'planner');
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $response = $this->getJson('/api/case/' . $case->uuid);
        $response->assertStatus(403);
    }

    public function testAccessCreatedCaseAsPlannerUserShouldWork(): void
    {
        $user = $this->createUser([], 'user,planner');
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $response = $this->getJson('/api/case/' . $case->uuid);
        $response->assertStatus(200);
    }

    public function testGetCaseShouldHaveDatesFormattedCorrectly(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user, [
            'date_of_symptom_onset' => CarbonImmutable::create(2021, 6, 6),
            'date_of_test' => CarbonImmutable::create(2021, 6, 7),
        ]);
        $response = $this->getJson('/api/case/' . $case->uuid);

        $response->assertStatus(200);
        $data = $response->json('case');
        $this->assertEquals('2021-06-06', $data['dateOfSymptomOnset']);
        $this->assertEquals('2021-06-07', $data['dateOfTest']);
    }

    public function testGetCaseWillAutomaticallyAddACCaseLock(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->getJson('/api/case/' . $case->uuid);

        $this->assertDatabaseHas('case_lock', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
        ]);
    }

    public function testCaseWithCaseLockCannotBeAccessedInEditMode(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCase($case);

        $response = $this->getJson('/api/case/' . $case->uuid);

        // If useCanEdit is false then the case will be loaded within view mode
        $this->assertFalse($response->json('case')['userCanEdit']);
    }

    public function testCaseWithCaseLockForUserCanBeAccessedInEditMode(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $this->createCaseLockForCaseAndUser($case, $user);

        $response = $this->getJson('/api/case/' . $case->uuid);

        // If useCanEdit is true then the case will be loaded within edit mode
        $this->assertTrue($response->json('case')['userCanEdit']);
    }

    public function testTaskCounts(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->createTaskForCase($case, [
            'task_group' => TaskGroup::contact(),
        ]);
        $this->createTaskForCase($case, [
            'task_group' => TaskGroup::positiveSource(),
        ]);

        $result = $this->be($user)->getJson(sprintf('/api/case/%s', $case->uuid));

        $expectedResult = [
            'contact' => 1,
            'positivesource' => 1,
            'symptomaticsource' => 0,
        ];
        $this->assertEquals($expectedResult, $result->json('case.taskCount'));
    }

    public function testContextCount(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $case = $this->createCaseForUser($user);
        $context = $this->createContextForCase($case);
        $this->createMomentForContext($context, [
            'day' => $case->date_of_test->format('Y-m-d'),
            'start_time' => '-10:00:00',
            'end_time' => '-00:00:01',
        ]);

        $response = $this->getJson('/api/case/' . $case->uuid);
        $this->assertEquals(1, $response->json('case')['contextContagiousCount']);
    }

    #[Group('policy')]
    public function testCaseResponseContainPolicyVersion(): void
    {
        $user = $this->createUser();
        $this->be($user);

        $case = $this->createCaseForUser($user, ['bco_status' => BCOStatus::open()]);

        $this
            ->getJson(sprintf('/api/case/%s', $case->uuid))
            ->assertStatus(200)
            ->assertJsonIsObject()
            ->assertJsonStructure([
                'case' => [
                    'policyVersion' => [
                        'uuid',
                        'name',
                        'startDate',
                    ],
                ],
            ]);
    }

    public function testUpdatePseudoBsn(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);

        $guid = '9fc3e93e-e24d-4064-5717-7b4b41cb8993';
        $censoredBsn = '******123';
        $letters = 'EJ';
        $pseudoBsnCollection = [
            new PseudoBsn($guid, $censoredBsn, $letters),
        ];
        $organisationExternalId = $organisation->external_id;

        $this->mock(
            BsnRepository::class,
            static function (MockInterface $mock) use ($guid, $pseudoBsnCollection, $organisationExternalId): void {
                $mock->expects('getByPseudoBsnGuid')
                    ->with($guid, $organisationExternalId)
                    ->andReturn($pseudoBsnCollection);
            },
        );

        $response = $this->be($user)->putJson(sprintf('api/cases/%s/pseudo-bsn', $case->uuid), [
            'pseudoBsnGuid' => $guid,
        ]);

        $response->assertStatus(200);
        $response->assertJson(static function (AssertableJson $json) use ($guid): AssertableJson {
            return $json
                ->where('case.pseudoBsnGuid', $guid)
                ->etc();
        });

        $indexFragmentResponse = $this->be($user)->getJson(sprintf('/api/cases/%s/fragments/index', $case->uuid));
        $indexFragmentResponse->assertJson(
            static function (AssertableJson $json) use ($censoredBsn, $letters): AssertableJson {
                return $json
                    ->where('data.bsnCensored', $censoredBsn)
                    ->where('data.bsnLetters', $letters)
                    ->etc();
            },
        );
    }

    #[DataProvider('contactStatusPermissionCheckProvider')]
    public function testUpdateContactStatusPermissionCheck(string $roles, array $payload, BCOStatus $BCOStatus, int $expectedStatus): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $roles);
        $case = $this->createCaseForUser($user, [
            'bco_status' => $BCOStatus,
        ]);

        $response = $this->be($user)->putJson(sprintf('api/cases/%s/contact-status', $case->uuid), $payload);
        $response->assertStatus($expectedStatus);
    }

    public static function contactStatusPermissionCheckProvider(): array
    {
        return [
            'casequality can put casequality_feedback if bco_status is completed' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::rejectAndReopen()->value,
                ],
                BCOStatus::completed(),
                200,
            ],
            'casequality must put casequality_feedback if bco_status is completed' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                ],
                BCOStatus::completed(),
                422,
            ],
            'casequality cannot put casequality_feedback if bco_status is open' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::complete()->value,
                ],
                BCOStatus::open(),
                422,
            ],
            'casequality must not put complete_status_checked if bco_status is open' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                ],
                BCOStatus::open(),
                200,
            ],
            'user must not put casequality_feedback' => [
                'user',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::complete()->value,
                ],
                BCOStatus::open(),
                422,
            ],
            'user can put without casequality_feedback' => [
                'user',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                ],
                BCOStatus::open(),
                200,
            ],
        ];
    }

    public function testUserWithoutCaseEditPermissionCannotUpdateContactStatus(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $user = $this->createUserForOrganisation($organisation);

        $response = $this->be($user)->putJson(
            sprintf('api/cases/%s/contact-status', $case->uuid),
            [
                UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
            ],
        );
        $response->assertStatus(403);
    }

    public function testPlannerFromAnotherOrganisationCannotUpdateContactStatus(): void
    {
        $case = $this->createCase();
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->putJson(
            sprintf('api/cases/%s/contact-status', $case->uuid),
            [
                UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
            ],
        );
        $response->assertStatus(404); // Note: 404 should become 403 once CaseAuthScope is removed
    }

    public function testUserWithoutCaseViewPermissionCannotGetContactStatus(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $user = $this->createUserForOrganisation($organisation);

        $response = $this->be($user)->getJson(
            sprintf('api/cases/%s/contact-status', $case->uuid),
        );
        $response->assertStatus(403);
    }

    public function testPlannerFromAnotherOrganisationCannotGetContactStatus(): void
    {
        $case = $this->createCase();
        $user = $this->createUser([], 'planner');

        $response = $this->be($user)->getJson(
            sprintf('api/cases/%s/contact-status', $case->uuid),
        );
        $response->assertStatus(404); // Note: 404 should become 403 once CaseAuthScope is removed
    }

    #[DataProvider('updateContactStatusDataProvider')]
    public function testUpdateContactStatus(string $roles, array $payload, array $caseAttributes, ?bool $expectedIsApproved, BCOStatus $expectedBcoStatus): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], $roles);
        $case = $this->createCaseForUser($user, $caseAttributes);

        $response = $this->be($user)->putJson(sprintf('api/cases/%s/contact-status', $case->uuid), $payload);
        $response->assertStatus(200);

        $case->refresh();
        $this->assertSame($expectedIsApproved, $case->isApproved);
        $this->assertSame($expectedBcoStatus->value, $case->bcoStatus->value);
    }

    public static function updateContactStatusDataProvider(): array
    {
        return [
            'update as user sets bcoStatus to complete if indexContactTracing is closed' => [
                'user',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::closedNoCollaboration(),
                ],
                [
                    'bco_status' => BCOStatus::open(),
                ],
                null,
                BCOStatus::completed(),
            ],
            'update as user sets bcoStatus to complete if indexContactTracing is complete' => [
                'user',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::completed(),
                ],
                [
                    'bco_status' => BCOStatus::open(),
                ],
                null,
                BCOStatus::completed(),
            ],
            'update as user sets bcoStatus to open if indexContactTracing is open' => [
                'user',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::notApproached(),
                ],
                [
                    'bco_status' => BCOStatus::completed(),
                    'index_status' => IndexStatus::delivered(),
                ],
                null,
                BCOStatus::open(),
            ],
            'approve and archive as casequality sets bcoStatus to archived' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::approveAndArchive(),
                ],
                [
                    'bco_status' => BCOStatus::completed(),
                ],
                true,
                BCOStatus::archived(),
            ],
            'archive as casequality sets bcoStatus to archived' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::new(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::approveAndArchive(),
                ],
                [
                    'bco_status' => BCOStatus::completed(),
                ],
                true,
                BCOStatus::archived(),
            ],
            'disapprove as casequality sets bcoStatus to open' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::closedNoCollaboration(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::rejectAndReopen(),
                ],
                [
                    'bco_status' => BCOStatus::completed(),
                ],
                false,
                BCOStatus::open(),
            ],
            'disapprove as casequality sets bcoStatus to open regardless the indexStatus' => [
                'casequality',
                [
                    UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::closedNoCollaboration(),
                    UpdateContactStatusRequest::FIELD_CASEQUALITY_FEEDBACK => CasequalityFeedback::rejectAndReopen(),
                ],
                [
                    'bco_status' => BCOStatus::completed(),
                    'index_status' => IndexStatus::initial(),
                ],
                false,
                BCOStatus::open(),
            ],
        ];
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testUpdateContactStatusWhenCaseCompleteShouldPushOsirisJobToQueue(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $payload = [
            UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::completed(),
        ];
        $caseAttributes = [
            'bco_status' => BCOStatus::open(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween('-5 months', '-31 days');
            }),
        ];

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user, $caseAttributes);

        $response = $this->be($user)->putJson(sprintf('api/cases/%s/contact-status', $case->uuid), $payload);
        $response->assertStatus(200);

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($case) {
            return $job->caseUuid === $case->uuid && $job->caseExportType === CaseExportType::DEFINITIVE_ANSWERS;
        });
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testUpdateContactStatusWhenCaseCompletedWithPreNotificationShouldPushOsirisJobToQueue(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $payload = [
            UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::completed(),
            UpdateContactStatusRequest::FIELD_FORCE_OSIRIS_NOTIFICATION => 'pre-notification',
        ];
        $caseAttributes = [
            'bco_status' => BCOStatus::open(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween('-5 months', '-31 days');
            }),
        ];

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user, $caseAttributes);

        $response = $this->be($user)->putJson(sprintf('api/cases/%s/contact-status', $case->uuid), $payload);
        $response->assertStatus(200);

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($case) {
            return $job->caseUuid === $case->uuid && $job->caseExportType === CaseExportType::INITIAL_ANSWERS;
        });
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testUpdateContactStatusWhenCaseReturnedShouldNotPushOsirisJobToQueue(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $payload = [
            UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::notStarted(),
        ];
        $caseAttributes = [
            'bco_status' => BCOStatus::open(),
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTimeBetween('-5 months', '-31 days');
            }),
        ];

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user, $caseAttributes);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $case->updatedAt,
            'osiris_status' => SoapMessageBuilder::NOTIFICATION_STATUS_INITIAL,
        ]);

        $response = $this->be($user)->putJson(sprintf('api/cases/%s/contact-status', $case->uuid), $payload);
        $response->assertStatus(200);

        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    #[Group('issue-BOOST-142')]
    public function testUpdateContactStatusShouldNotAddReopendCaseNoteWhenCaseIsReturned(): void
    {
        $contactStatusPayload = [
            UpdateContactStatusRequest::FIELD_STATUS_INDEX_CONTACT_TRACING => ContactTracingStatus::notStarted(),
        ];

        $user = $this->createUserWithOrganisation(roles: 'user,planner');
        $case = $this->createCaseForUser($user, ['bco_status' => BCOStatus::open()]);

        $this->be($user);

        $this
            ->putJson(sprintf('api/cases/%s/contact-status', $case->uuid), $contactStatusPayload)
            ->assertStatus(200);

        $timeline = $this
            ->get(sprintf('api/cases/%s/planner-timeline', $case->uuid))
            ->assertSuccessful()
            ->assertJsonStructure(['*' => ['timelineable_type', 'time']]);

        $casesReopenedNotes = Collection::make($timeline->json())
            ->filter(static fn (array $entry): bool => $entry['timelineable_type'] === 'note')
            ->filter(static fn (array $entry): bool => Str::of($entry['time'])->contains('case heropend', ignoreCase: true));

        $this->assertCount(0, $casesReopenedNotes, 'Expected zero cases reopend notes');
    }

    #[DataProvider('archiveSingleCaseDataProvider')]
    public function testArchiveSingleCaseDirectly(
        BCOStatus $bcoStatus,
        bool $shouldAssignedUser,
        bool $inQueue,
        bool $withNote,
        bool $expectedArchived,
        BCOStatus $expectedBcoStatus,
        int $expectedResponseStatus,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        if ($inQueue) {
            $queueUuid = $this->createCaseList([
                'is_queue' => true,
            ])->uuid;
        }

        $assignedUserUuid = $shouldAssignedUser
            ? $this->createUserForOrganisation($organisation)->uuid
            : null;

        $case = $this->createCaseForOrganisation($organisation, [
            'assigned_user_uuid' => $assignedUserUuid,
            'assigned_case_list_uuid' => $queueUuid ?? null,
            'bco_status' => $bcoStatus,
        ]);

        $data = [];
        // set data note to null or empty
        $data['note'] = $withNote ? $this->faker->sentence() : null;

        $response = $this->putJson(sprintf('/api/cases/%s/archive', $case->uuid), $data);

        // We will expect a 200 status as return
        $response->assertStatus($expectedResponseStatus);

        // We want to check the case that should have been updated to have the BCOStatus as archived
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'bco_status' => $expectedBcoStatus->value,
            'assigned_user_uuid' => $expectedArchived ? null : $assignedUserUuid,
        ]);

        // Assert that note has been saved if given
        if ($data['note'] && $expectedArchived) {
            $this->assertDatabaseHas('note', [
                'case_uuid' => $case->uuid,
            ]);
        } else {
            $this->assertDatabaseMissing('note', [
                'case_uuid' => $case->uuid,
            ]);
        }
    }

    public static function archiveSingleCaseDataProvider(): Generator
    {
        yield 'Open BCO Status when not assigned should be archived with note' => [
            BCOStatus::open(),
            false,
            false,
            true,
            true,
            BCOStatus::archived(),
            200,
        ];

        yield 'Open BCO Status when not assigned should be archived without note' => [
            BCOStatus::open(),
            false,
            false,
            false,
            true,
            BCOStatus::open(),
            422,
        ];

        yield 'Archived BCO Status when not assigned should not be archived without note' => [
            BCOStatus::archived(),
            true,
            true,
            false,
            false,
            BCOStatus::archived(),
            422,
        ];
    }

    #[Group('osiris')]
    #[Group('archive-without-notification')]
    public function testArchivingSingleCaseWithoutNotifyingOsiris(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();
        Event::fake([
            CaseValidationRaisesWarning::class,
            CaseValidationRaisesNotice::class,
        ]);

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $indexSchemaVersion = EloquentCase::getSchema()
            ->getCurrentVersion()
            ->getField('index')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();

        $case = $this->createCaseForOrganisation($organisation, [
            'index' => Index::newInstanceWithVersion($indexSchemaVersion, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
            }),
        ]);

        $data = [
            'note' => $this->faker->sentences(asText: true),
            'sendOsirisNotification' => false,
        ];

        $response = $this->putJson(sprintf('/api/cases/%s/archive', $case->uuid), $data);

        $response->assertSuccessful();
        Event::assertNotDispatched(CaseValidationRaisesWarning::class);
        Event::assertNotDispatched(CaseValidationRaisesNotice::class);
        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    #[Group('osiris')]
    #[Group('archive-without-notification')]
    public function testArchivingMultipleCasesWithoutNotifyingOsiris(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();
        Event::fake([
            CaseValidationRaisesWarning::class,
            CaseValidationRaisesNotice::class,
        ]);

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $indexSchemaVersion = EloquentCase::getSchema()
            ->getCurrentVersion()
            ->getField('index')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();

        $cases = [
            $this->createCaseForOrganisation($organisation, [
                'index' => Index::newInstanceWithVersion($indexSchemaVersion, function (Index $index): void {
                    $index->dateOfBirth = $this->faker->dateTime();
                }),
            ]),
            $this->createCaseForOrganisation($organisation, [
                'index' => Index::newInstanceWithVersion($indexSchemaVersion, function (Index $index): void {
                    $index->dateOfBirth = $this->faker->dateTime();
                }),
            ]),
        ];

        $data = [
            'cases' => array_map(static fn (EloquentCase $case): string => $case->uuid, $cases),
            'note' => $this->faker->sentences(asText: true),
            'sendOsirisNotification' => false,
        ];

        $response = $this->putJson('/api/cases/archiveMulti', $data);

        $response->assertSuccessful();
        Event::assertNotDispatched(CaseValidationRaisesWarning::class);
        Event::assertNotDispatched(CaseValidationRaisesNotice::class);
        Queue::assertNotPushed(ExportCaseToOsiris::class);
    }

    public function testArchiveCaseShouldUnassignedCaseListIfItIsQueue(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $queueListUuid = $this->createCaseList([
            'is_queue' => true,
        ])->uuid;

        $case = $this->createCaseForOrganisation($organisation, [
            'assigned_case_list_uuid' => $queueListUuid,
            'bco_status' => BCOStatus::completed(),
        ]);

        $response = $this->putJson(sprintf('/api/cases/%s/archive', $case->uuid), ['note' => $this->faker->sentence]);

        // We will expect a 200 status as return
        $response->assertStatus(200);

        // We want to check the case that should have been updated to have the BCOStatus as archived
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'bco_status' => BCOStatus::archived(),
            'assigned_case_list_uuid' => null,
        ]);
    }

    #[DataProvider('archiveMultipleCasesDataProvider')]
    public function testArchiveMultipleCasesDirectlyAndSkipsInvalidCases(
        array $cases,
        int $expectedInvalidCases,
        int $expectedStatus,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $invalidCaseList = [];
        // Create cases that have been defined within the data provider
        foreach ($cases as $key => $case) {
            $eloquentCase = $this->createCaseForOrganisation($organisation, [
                'assigned_user_uuid' => $case['assigned_user_uuid']
                    ? $this->createUserForOrganisation($organisation) : null,
                'bco_status' => $case['bco_status'],
            ]);

            if (!$case['expected_archived']) {
                $invalidCaseList[$eloquentCase->caseId] = $eloquentCase->uuid;
            }

            $cases[$key]['eloquentCase'] = $eloquentCase;
        }

        // Make the request
        $response = $this->putJson('/api/cases/archiveMulti', [
            'note' => $this->faker->sentence(),
            'cases' => array_map(static fn($case) => $case['eloquentCase']->uuid, $cases),
        ]);

        // Assert the response that we get back from the request
        $response->assertStatus($expectedStatus)
            ->assertJsonCount($expectedInvalidCases, 'invalid_cases')
            ->assertJson(['invalid_cases' => $invalidCaseList]);

        foreach ($cases as $case) {
            if ($case['expected_archived']) {
                // Is archived
                $this->assertDatabaseHas('covidcase', [
                    'uuid' => $case['eloquentCase']->uuid,
                    'bco_status' => $case['expected_bco_status']->value,
                ]);
                $this->assertDatabaseHas('note', [
                    'case_uuid' => $case['eloquentCase']->uuid,
                ]);
            } else {
                // Is not archived
                $this->assertDatabaseHas('covidcase', [
                    'uuid' => $case['eloquentCase']->uuid,
                    'bco_status' => $case['expected_bco_status']->value,
                ]);
            }
        }
    }

    public function testArchiveCaseWithContextWillDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::open(),
        ]);

        $this->createContextForPlace($this->createPlace(), [
            'covidcase_uuid' => $case->uuid,
        ]);

        $this->putJson(sprintf('/api/cases/%s/archive', $case->uuid), [
            'note' => $this->faker->sentence(),
        ]);

        Bus::assertDispatched(UpdatePlaceCounters::class);
    }

    public function testArchiveCaseWithoutContextWillNotDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::open(),
        ]);

        $this->putJson(sprintf('/api/cases/%s/archive', $case->uuid), [
            'note' => $this->faker->sentence(),
        ]);

        Bus::assertNotDispatched(UpdatePlaceCounters::class);
    }

    public function testReopenCaseWithContextWillDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::archived(),
        ]);

        $this->createContextForPlace($this->createPlace(), [
            'covidcase_uuid' => $case->uuid,
        ]);

        $this->putJson(sprintf('/api/cases/%s/reopen', $case->uuid), [
            'note' => $this->faker->sentence(),
        ]);

        Bus::assertDispatched(UpdatePlaceCounters::class);
    }

    public function testReopenCaseWithoutContextWillNotDispatchUpdatePlaceCountersJob(): void
    {
        Bus::fake(UpdatePlaceCounters::class);

        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->be($user);

        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::archived(),
        ]);

        $this->putJson(sprintf('/api/cases/%s/reopen', $case->uuid), [
            'note' => $this->faker->sentence(),
        ]);

        Bus::assertNotDispatched(UpdatePlaceCounters::class);
    }

    public static function archiveMultipleCasesDataProvider(): Generator
    {
        yield 'mixed types of cases which are valid & invalid' => [
            'cases' => [
                [
                    'assigned_user_uuid' => false,
                    'bco_status' => BCOStatus::open(),
                    'expected_archived' => true,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => true,
                    'bco_status' => BCOStatus::open(),
                    'expected_archived' => true,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => false,
                    'bco_status' => BCOStatus::completed(),
                    'expected_archived' => true,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => true,
                    'bco_status' => BCOStatus::completed(),
                    'expected_archived' => true,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => false,
                    'bco_status' => BCOStatus::archived(),
                    'expected_archived' => false,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => true,
                    'bco_status' => BCOStatus::archived(),
                    'expected_archived' => false,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
            ],
            'expected_invalid_cases' => 2,
            'expected_status' => 200,
        ];

        yield 'types of cases which are all valid' => [
            'cases' => [
                [
                    'assigned_user_uuid' => false,
                    'bco_status' => BCOStatus::open(),
                    'expected_archived' => true,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => false,
                    'bco_status' => BCOStatus::completed(),
                    'expected_archived' => true,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
            ],
            'expected_invalid_cases' => 0,
            'expected_status' => 200,
        ];

        yield 'types of cases which are all invalid' => [
            'cases' => [
                [
                    'assigned_user_uuid' => false,
                    'bco_status' => BCOStatus::archived(),
                    'expected_archived' => false,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
                [
                    'assigned_user_uuid' => true,
                    'bco_status' => BCOStatus::archived(),
                    'expected_archived' => false,
                    'expected_bco_status' => BCOStatus::archived(),
                ],
            ],
            'expected_invalid_cases' => 2,
            'expected_status' => 400,
        ];
    }

    #[DataProvider('reopenCaseDataProvider')]
    #[Group('issue-BOOST-112')]
    public function testReopenCase(
        array $caseAttributes,
        bool $withNote,
        bool $expectReopened,
    ): void {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation, [], 'planner');
        $this->actingAs($user);

        // Create case to be reopened
        $case = $this->createCaseForOrganisation($organisation, array_merge($caseAttributes, [
            'updated_at' => CarbonImmutable::now(),
        ]));

        // Create payload with note
        $payload = $withNote ? ['note' => $this->faker->sentence] : [];

        // Make the request
        $response = $this->patchJson(sprintf('api/cases/%s/reopen', $case->uuid), $payload);

        // Assert that return status is correct
        $response->assertStatus($expectReopened ? 200 : 403);

        if ($expectReopened) {
            $response->assertStatus(200);
            $this->assertDatabaseHas('covidcase', [
                'uuid' => $case->uuid,
                'bco_status' => BCOStatus::open(),
                'is_approved' => null,
            ]);
            $this->assertDatabaseHas('note', [
                'case_uuid' => $case->uuid,
                'type' => CaseNoteType::caseReopened(),
            ]);
        } else {
            $response->assertStatus(403);
            $this->assertDatabaseHas('covidcase', [
                'uuid' => $case->uuid,
                'bco_status' => $case->bcoStatus,
                'is_approved' => $case->is_approved,
            ]);
            $this->assertDatabaseMissing('note', [
                'case_uuid' => $case->uuid,
            ]);
        }
    }

    public static function reopenCaseDataProvider(): Generator
    {
        // Completed cases can only be reopen on special conditions
        yield 'Completed case while not Approved without note' => [
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => null,
            ],
            false,
            true,
        ];

        // Completed cases can only be reopen on special conditions
        yield 'Completed case while not Approved with note' => [
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => null,
            ],
            true,
            true,
        ];

        yield 'Completed case while Rejected without note' => [
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => false,
            ],
            false,
            true,
        ];

        yield 'Completed case while Rejected with note' => [
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => false,
            ],
            true,
            true,
        ];

        yield 'Completed case while Approved without note' => [
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => true,
            ],
            false,
            true,
        ];

        yield 'Completed case while Approved with note' => [
            [
                'bco_status' => BCOStatus::completed(),
                'is_approved' => true,
            ],
            true,
            true,
        ];

        // Archived cases can always be reopened
        yield 'Archived case while not Approved without note' => [
            [
                'bco_status' => BCOStatus::archived(),
                'is_approved' => null,
            ],
            false,
            true,
        ];

        yield 'Archived case while not Approved with note' => [
            [
                'bco_status' => BCOStatus::archived(),
                'is_approved' => null,
            ],
            true,
            true,
        ];

        yield 'Archived case while Rejected without note' => [
            [
                'bco_status' => BCOStatus::archived(),
                'is_approved' => false,
            ],
            false,
            true,
        ];

        yield 'Archived case while Rejected with note' => [
            [
                'bco_status' => BCOStatus::archived(),
                'is_approved' => false,
            ],
            true,
            true,
        ];

        yield 'Archived case while Approved without note' => [
            [
                'bco_status' => BCOStatus::archived(),
                'is_approved' => true,
            ],
            false,
            true,
        ];

        yield 'Archived case while Approved with note' => [
            [
                'bco_status' => BCOStatus::archived(),
                'is_approved' => true,
            ],
            true,
            true,
        ];

        // Open & Draft can't be reopened
        yield 'Open case without note' => [['bco_status' => BCOStatus::open()], false, false];
        yield 'Open case with note' => [['bco_status' => BCOStatus::open()], true, false];
        yield 'Draft case without note' => [['bco_status' => BCOStatus::draft()], false, false];
        yield 'Draft case with note' => [['bco_status' => BCOStatus::draft()], true, false];
    }

    public function testBcoUserCanOnlyOpenCaseAssignedToIt(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $user = $this->createUserForOrganisation($organisation);

        $this->be($user);

        $response = $this->getJson('/api/case/' . $case->uuid);
        $response->assertStatus(403);

        $case->assigned_user_uuid = $user->uuid;
        $case->save();

        $response = $this->getJson('/api/case/' . $case->uuid);
        $response->assertStatus(200);
    }

    #[DataProvider('updateCurrentCaseBcoPhaseDataProvider')]
    public function testUpdateCurrentCaseBcoPhase(
        string $userRole,
        BCOPhase $initialBcoPhase,
        string $payloadBcoPhaseValue,
        string $expectedBcoPhaseValue,
        int $expectedStatusCode,
    ): void {
        $user = $this->createUser([], $userRole, [
            'bco_phase' => $initialBcoPhase,
        ]);

        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson('/api/cases/' . $case->uuid . '/bcophase', [
            'bco_phase' => $payloadBcoPhaseValue,
        ]);

        $response->assertStatus($expectedStatusCode);
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'bco_phase' => $expectedBcoPhaseValue,
        ]);
    }

    public static function updateCurrentCaseBcoPhaseDataProvider(): Generator
    {
        yield 'User | Accepted change BcoPhase `phase2` from `phase1`' => [
            'user',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase2()->value,
            200,
        ];

        yield 'Planner | accepted change BcoPhase `phase2` from `phase1`' => [
            'planner',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase2()->value,
            200,
        ];

        yield 'Case quality checker | Accepted change BcoPhase `phase2` from `phase1`' => [
            'casequality',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase2()->value,
            200,
        ];

        yield 'Case quality checker | Declined change BcoPhase that does not exists' => [
            'casequality',
            BCOPhase::phase1(),
            'teapot',
            BCOPhase::phase1()->value,
            422,
        ];

        yield 'Medical Supervisor | Declined change BcoPhase due to incorrect permissions' => [
            'medical_supervisor',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase1()->value,
            403,
        ];
    }

    #[DataProvider('invalidBcoPhaseValueDataProvider')]
    public function testUpdateCaseBcoPhaseWithInvalidBcoPhaseFails(string|int|null $invalidBcoPhaseValue): void
    {
        $user = $this->createUser([], 'planner');
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson(sprintf('/api/cases/%s/bcophase', $case->uuid), [
            'bco_phase' => $invalidBcoPhaseValue,
        ]);

        $expectedResponse = [
            'message' => 'Veld "Bco phase" is ongeldig.',
            'errors' => [
                'bco_phase' => [
                    'Veld "Bco phase" is ongeldig.',
                ],
            ],
        ];

        $response->assertStatus(422);
        $this->assertSame($expectedResponse, $response->json());
    }

    #[DataProvider('updateMultipleCaseBcoPhaseDataProvider')]
    public function testUpdateMultipleCaseBcoPhase(
        string $userRole,
        BCOPhase $initialBcoPhase,
        string $payloadBcoPhaseValue,
        string $expectedBcoPhaseValue,
        int $expectedStatusCode,
    ): void {
        $user = $this->createUser([], $userRole, [
            'bco_phase' => $initialBcoPhase,
        ]);

        $case1 = $this->createCaseForUser($user);
        $case2 = $this->createCaseForUser($user);

        $response = $this->be($user)->putJson('/api/cases/multi/bcophase', [
            'bco_phase' => $payloadBcoPhaseValue,
            'cases' => [$case1->uuid, $case2->uuid],
        ]);

        $response->assertStatus($expectedStatusCode);
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case1->uuid,
            'bco_phase' => $expectedBcoPhaseValue,
        ]);
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case2->uuid,
            'bco_phase' => $expectedBcoPhaseValue,
        ]);
    }

    public static function updateMultipleCaseBcoPhaseDataProvider(): Generator
    {
        yield 'User | Forbidden change BcoPhase `phase2` from `phase1`' => [
            'user',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase1()->value,
            403,
        ];

        yield 'Planner | accepted change BcoPhase `phase2` from `phase1`' => [
            'planner',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase2()->value,
            200,
        ];

        yield 'Planner | Declined change BcoPhase that does not exists' => [
            'planner',
            BCOPhase::phase1(),
            'teapot',
            BCOPhase::phase1()->value,
            422,
        ];

        yield 'Case quality checker | Forbidden change BcoPhase `phase2` from `phase1`' => [
            'casequality',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase1()->value,
            403,
        ];

        yield 'Medical Supervisor | Declined change BcoPhase due to incorrect permissions' => [
            'medical_supervisor',
            BCOPhase::phase1(),
            BCOPhase::phase2()->value,
            BCOPhase::phase1()->value,
            403,
        ];
    }

    public function testBcoUserCanOnlyAssignPhaseToAccessibleCase(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);
        $user = $this->createUserForOrganisation($organisation);

        $this->be($user);

        $response = $this->putJson(sprintf('/api/cases/%s/bcophase', $case->uuid), [
            'bco_phase' => BCOPhase::phase1()->value,
        ]);
        $response->assertStatus(403);

        $case->assigned_user_uuid = $user->uuid;
        $case->save();

        $response = $this->putJson(sprintf('/api/cases/%s/bcophase', $case->uuid), [
            'bco_phase' => BCOPhase::phase1()->value,
        ]);
        $response->assertStatus(200);
    }

    public function testUpdateCaseOrganisationUpdatesOrganisation(): void
    {
        // Create organisations and case
        $initialOrganisation = $this->createOrganisation();
        $targetOrganisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($initialOrganisation);

        // Create and be the user for the initial organisation
        $user = $this->createUserForOrganisation($initialOrganisation, [], 'planner');
        $this->be($user);

        // Make the call
        $response = $this->post(sprintf('/api/case/%s/update-organisation', $case->uuid), [
            'organisation_uuid' => $targetOrganisation->uuid,
        ]);

        // Assert response status
        $response->assertStatus(200);

        // Assert that case has changed from organisation
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'organisation_uuid' => $targetOrganisation->uuid,
        ]);
    }

    public function testUpdateCaseOrganisationCreatesNote(): void
    {
        // Create organisations and case
        $initialOrganisation = $this->createOrganisation();
        $targetOrganisation = $this->createOrganisation();

        $case = $this->createCaseForOrganisation($initialOrganisation);

        // Create and be the user for the initial organisation
        $user = $this->createUserForOrganisation($initialOrganisation, [], 'planner');
        $this->be($user);

        // create a random note
        $note = $this->faker->sentence;

        // Make the call
        $response = $this->post(sprintf('/api/case/%s/update-organisation', $case->uuid), [
            'organisation_uuid' => $targetOrganisation->uuid,
            'note' => $note,
        ]);

        // Assert response status
        $response->assertStatus(200);

        // Assert database has note for case
        $this->assertDatabaseHas('note', [
            'case_uuid' => $case->uuid,
            'user_uuid' => $user->uuid,
            'type' => CaseNoteType::caseChangedOrganisation()->value,
        ]);
    }

    public function testUpdateCaseOrganisationSetsAssignmentsToNull(): void
    {
        // Create organisations and case
        $initialOrganisation = $this->createOrganisation();
        $targetOrganisation = $this->createOrganisation();

        $assignedUser = $this->createUser();
        $assignedCaseList = $this->createCaseList();
        $assignedOrganisation = $this->createOrganisation();

        // Create case with assignments
        $case = $this->createCaseForOrganisation($initialOrganisation, [
            'assigned_user_uuid' => $assignedUser->uuid,
            'assigned_case_list_uuid' => $assignedCaseList->uuid,
            'assigned_organisation_uuid' => $assignedOrganisation->uuid,
        ]);

        // Create and be the user for the initial organisation
        $user = $this->createUserForOrganisation($initialOrganisation, [], 'planner');
        $this->be($user);

        // Make the call
        $response = $this->post(sprintf('/api/case/%s/update-organisation', $case->uuid), [
            'organisation_uuid' => $targetOrganisation->uuid,
        ]);

        // Assert response status
        $response->assertStatus(200);

        // Make sure every assignment is non-existing
        $this->assertDatabaseMissing('covidcase', ['assigned_user_uuid' => $assignedUser->uuid]);
        $this->assertDatabaseMissing('covidcase', ['assigned_case_list_uuid' => $assignedCaseList->uuid]);
        $this->assertDatabaseMissing('covidcase', ['assigned_organisation_uuid' => $assignedOrganisation->uuid]);

        // And every field is null (And not something else)
        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'assigned_user_uuid' => null,
            'assigned_case_list_uuid' => null,
            'assigned_organisation_uuid' => null,
        ]);
    }

    public static function invalidBcoPhaseValueDataProvider(): array
    {
        return [
            'non-enum value' => ['invalid'],
            'integer' => [123],
            'null' => [null],
        ];
    }

    public function testCreateNoteAsCallcenter(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $this->be($user);
        $organisation = $user->getOrganisation();

        $case = $this->createCaseForOrganisation($organisation);
        $assignmentTokenService = $this->app->get(AssignmentTokenService::class);

        $response = $this->post(sprintf('/api/cases/%s/notes', $case->uuid), [
            'note' => $this->faker->sentence(),
            'type' => CaseNoteType::caseNoteIndexBySearch()->value,
        ], [
            'Assignment-Token' => $assignmentTokenService->createToken(
                Collection::make([
                    new TokenResource(mod: AssignmentModelEnum::Note, ids: [$case->uuid]),
                ]),
                $user,
            ),
        ]);

        $response->assertStatus(201);
    }

    public function testCreateNoteAsUnauthorizedCallcenter(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter');
        $this->be($user);
        $organisation = $user->getOrganisation();

        $case = $this->createCaseForOrganisation($organisation);

        // Make the call
        $response = $this->post(sprintf('/api/cases/%s/notes', $case->uuid), [
            'note' => $this->faker->sentence(),
            'type' => CaseNoteType::caseNoteIndexBySearch()->value,
        ]);

        // Assert response status
        $response->assertStatus(403);
    }

    public function testCreateNoteAsCallcenterExpert(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter_expert');
        $this->be($user);
        $organisation = $user->getOrganisation();

        $case = $this->createCaseForOrganisation($organisation);
        $assignmentTokenService = $this->app->get(AssignmentTokenService::class);

        $response = $this->post(sprintf('/api/cases/%s/notes', $case->uuid), [
            'note' => $this->faker->sentence(),
            'type' => CaseNoteType::caseNoteIndexBySearch()->value,
        ], [
            'Assignment-Token' => $assignmentTokenService->createToken(
                Collection::make([
                    new TokenResource(mod: AssignmentModelEnum::Case_, ids: [$case->uuid]),
                ]),
                $user,
            ),
        ]);

        $response->assertStatus(201);
    }

    public function testCreateNoteAsUnauthorizedCallcenterExpert(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'callcenter_expert');
        $this->be($user);
        $organisation = $user->getOrganisation();

        $case = $this->createCaseForOrganisation($organisation);

        // Make the call
        $response = $this->post(sprintf('/api/cases/%s/notes', $case->uuid), [
            'note' => $this->faker->sentence(),
            'type' => CaseNoteType::caseNoteIndexBySearch()->value,
        ]);

        // Assert response status
        $response->assertStatus(403);
    }

    public function testCreateNoteAsPlanner(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'planner');
        $this->be($user);
        $organisation = $user->getOrganisation();

        $case = $this->createCaseForOrganisation($organisation);

        // Make the call
        $response = $this->post(sprintf('/api/cases/%s/notes', $case->uuid), [
            'note' => $this->faker->sentence(),
            'type' => CaseNoteType::caseNote()->value,
        ]);

        // Assert response status
        $response->assertStatus(201);
    }

    public function testCreateNoteAsUser(): void
    {
        $user = $this->createUserWithOrganisation();
        $this->be($user);
        $organisation = $user->getOrganisation();

        $case = $this->createCaseForOrganisation($organisation);

        $response = $this->post(sprintf('/api/cases/%s/notes', $case->uuid), [
            'note' => $this->faker->sentence(),
            'type' => CaseNoteType::caseNote()->value,
        ]);

        $response->assertStatus(403);
    }

    public function testCheckUnansweredQuestions(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);

        $version = $this->faker->optional()->randomElement(['finished', 'pre-notification']);

        $response = $this->be($user)->get(
            sprintf('api/cases/%s/check-unanswered-questions?version=%s', $case->uuid, $version),
        );

        $response->assertSuccessful();
    }

    public function testCheckUnansweredQuestionsWithInvalidVersion(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForUser($user);

        $response = $this->be($user)->get(
            sprintf('api/cases/%s/check-unanswered-questions?version=%s', $case->uuid, $this->faker->word()),
        );

        $response->assertBadRequest();
    }

    /**
     * @param array<BCOStatus> $createWithBcoStatus
     */
    private function createCases(EloquentUser $user, array $createWithBcoStatus): void
    {
        foreach ($createWithBcoStatus as $bcoStatus) {
            $this->createCaseForUser($user, [
                'bco_status' => $bcoStatus,
            ]);
        }
    }
}
