<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Exceptions\UpdateOrganisationUnauthorizedException;
use App\Jobs\ExportCaseToOsiris;
use App\Models\CovidCase;
use App\Models\CovidCase\Contact;
use App\Models\CovidCase\Index;
use App\Models\CovidCase\PlannerCase;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\Timeline;
use App\Models\Enums\Osiris\CaseExportType;
use App\Models\ValueObjects\CaseIdentifier;
use App\Repositories\Bsn\BsnException;
use App\Repositories\Bsn\BsnRepository;
use App\Repositories\Bsn\BsnServiceException;
use App\Repositories\Bsn\Dto\PseudoBsn;
use App\Repositories\CasePlanningData;
use App\Repositories\CaseStatusRepository;
use App\Schema\Types\SchemaType;
use App\Services\Bsn\BsnService;
use App\Services\CaseService;
use App\Services\Note\CaseNoteService;
use Carbon\CarbonImmutable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Queue;
use MinVWS\DBCO\Enum\Models\AutomaticAddressVerificationStatus;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use MinVWS\DBCO\Enum\Models\CaseNoteType;
use MinVWS\DBCO\Enum\Models\CasequalityFeedback;
use MinVWS\DBCO\Enum\Models\ContactTracingStatus;
use MinVWS\DBCO\Enum\Models\IndexStatus;
use MinVWS\DBCO\Enum\Models\Priority;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;
use Tests\Helpers\ConfigHelper;

use function app;
use function collect;
use function config;

#[Group('case')]
#[Group('case-service')]
class CaseServiceTest extends FeatureTestCase
{
    private CaseService $caseService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->caseService = app(CaseService::class);
        $this->caseNoteService = app(CaseNoteService::class);
    }

    public function emptyCaseData(): CasePlanningData
    {
        return new CasePlanningData(
            null,
            null,
            null,
            Priority::none(),
            [],
            null,
            null,
            $this->faker->randomElement(AutomaticAddressVerificationStatus::all()),
        );
    }

    public function testGetCaseNotFound(): void
    {
        $this->expectException(ModelNotFoundException::class);
        $this->caseService->getCase($this->faker->uuid);
    }

    public function testGetCaseWillReturnCovidCaseInstance(): void
    {
        $eloquentCase = $this->createCase();

        $this->assertInstanceOf(CovidCase::class, $this->caseService->getCase($eloquentCase->uuid));
    }

    public function testGetCovidCaseFromEloquentModelWillReturnCovidCaseInstance(): void
    {
        $eloquentCase = $this->createCase();

        $this->assertInstanceOf(CovidCase::class, $this->caseService->getCovidCaseFromEloquentModel($eloquentCase));
    }

    #[DataProvider('completedContactStatusProvider')]
    #[Group('contact-status')]
    public function testBcoStatusReconciliationOnContactStatusCompletionOrArchived(
        ContactTracingStatus $statusIndexContactTracing,
        BCOStatus $currentBcoStatus,
        ?CasequalityFeedback $casequalityFeedback,
        BCOStatus $expectedStatus,
        ?bool $expectedIsApproved,
        ?string $statusExplanation,
    ): void {
        $expectedCompletionDate = "2021-03-30T00:00:00+00:00";
        CarbonImmutable::setTestNow($expectedCompletionDate);
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::open(),
            'completed_at' => null,
        ]);
        $user = $this->createUserForOrganisation($organisation);
        $this->be($user);

        $case->assigned_user_uuid = $user->uuid;
        $case->bcoStatus = $currentBcoStatus;
        $this->caseService->updateIndexContactStatus($case, $statusIndexContactTracing, $statusExplanation, $casequalityFeedback);

        $case->refresh();
        $this->assertEquals($expectedStatus, $case->bcoStatus);
        $this->assertNull($case->assigned_user_uuid);
        if ($case->bcoStatus === BCOStatus::completed()) {
            $this->assertEquals($expectedCompletionDate, $case->completedAt->toIso8601String());
        } else {
            $this->assertNull($case->completedAt);
        }
        $this->assertSame($expectedIsApproved, $case->isApproved);
    }

    public static function completedContactStatusProvider(): array
    {
        return [
            'calling completeBco sets bco status to completed' => [
                ContactTracingStatus::closedNoCollaboration(),
                BCOStatus::open(),
                null,
                BCOStatus::completed(),
                null,
                null,
            ],
            'calling completeBco with casequalityFeedback::approveAndArchive sets the bco status to archived and isApproved to true' => [
                ContactTracingStatus::closedNoCollaboration(),
                BCOStatus::completed(),
                CasequalityFeedback::approveAndArchive(),
                BCOStatus::archived(),
                true,
                null,
            ],
            'calling completeBco with casequalityFeedback::rejectAndReopen sets isApproved to false and keeps the bco status open' => [
                ContactTracingStatus::closedNoCollaboration(),
                BCOStatus::open(),
                CasequalityFeedback::rejectAndReopen(),
                BCOStatus::open(),
                false,
                null,
            ],
            'calling completeBco with casequalityFeedback::archive sets isApproved to null and the bco status to archived' => [
                ContactTracingStatus::closedNoCollaboration(),
                BCOStatus::open(),
                CasequalityFeedback::archive(),
                BCOStatus::archived(),
                null,
                null,
            ],
            'calling completeBco with casequalityFeedback::complete sets isApproved to null and the bco status to completed' => [
                ContactTracingStatus::closedNoCollaboration(),
                BCOStatus::open(),
                CasequalityFeedback::complete(),
                BCOStatus::completed(),
                null,
                null,
            ],
            'calling completeBco with casequalityFeedback null sets isApproved to null' => [
                ContactTracingStatus::completed(),
                BCOStatus::open(),
                null,
                BCOStatus::completed(),
                null,
                null,
            ],
        ];
    }

    #[Group('contact-status')]
    public function testCaseGivenBackToWorkDistributorOnContactStatusNotCompleted(): void
    {
        $user = $this->createUser();
        $case = $this->createCaseForUser($user);
        $this->be($user);

        $case->bcoStatus = BCOStatus::draft();
        $this->caseService->updateIndexContactStatus(
            $case,
            ContactTracingStatus::conversationStarted(),
            null,
            CasequalityFeedback::rejectAndReopen(),
        );

        $this->assertNull($case->assigned_user_uuid);
    }

    #[DataProvider('pairingAllowedDataProvider')]
    public function testPairingAllowed(int $subSeconds, bool $expectedResult): void
    {
        $pairingAllowedInterval = 10;
        config()->set('misc.case.pairingAllowedInterval', $pairingAllowedInterval);
        CarbonImmutable::setTestNow('2020-01-01');

        $user = $this->createUser();
        $case = $this->createCaseForUser($user, [
            'created_at' => CarbonImmutable::now()->subSeconds($pairingAllowedInterval + $subSeconds),
        ]);

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);

        $this->assertEquals($expectedResult, $caseService->isPairingAllowed($case));
    }

    public static function pairingAllowedDataProvider(): array
    {
        return [
            '-1 second from allowed' => [-1, true],
            'equal from allowed' => [0, false],
            '+1 second from allowed' => [+1, false],
        ];
    }

    public function testUpdatePseudoBsnFailsWhenNoResults(): void
    {
        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')
                ->andReturn([]);
        });

        $organisation = new EloquentOrganisation();
        $organisation->external_id = 'foo';

        $covidCase = new EloquentCase();
        $covidCase->organisation = $organisation;

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);

        $this->expectException(BsnException::class);
        $this->expectExceptionMessage('failed converting bsn to pseudo bsn: none returned');
        $caseService->updatePseudoBsn($covidCase, 'foo');
    }

    public function testUpdatePseudoBsnFailsWhenTooManyResults(): void
    {
        $this->mock(BsnRepository::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')
                ->andReturn([
                    new PseudoBsn('', '', ''),
                    new PseudoBsn('', '', ''),
                ]);
        });

        $organisation = new EloquentOrganisation();
        $organisation->external_id = 'foo';

        $covidCase = new EloquentCase();
        $covidCase->organisation = $organisation;

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);

        $this->expectException(BsnException::class);
        $this->expectExceptionMessage('failed converting bsn to pseudo bsn: multiple results found');
        $caseService->updatePseudoBsn($covidCase, 'foo');
    }

    /**
     * @throws BsnException
     */
    public function testUpdatePseudoBsnSetsAutomaticAddressVerificationStatusToVerified(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation, [
            'automatic_address_verification_status' => AutomaticAddressVerificationStatus::unchecked(),
        ]);

        $this->mock(BsnService::class, function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')
                ->andReturn(new PseudoBsn($this->faker->uuid(), $this->faker->numerify('******###'), $this->faker->randomLetter()));
        });

        $this->be($user);
        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);
        $caseService->updatePseudoBsn($case, $this->faker->uuid());

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'automatic_address_verification_status' => AutomaticAddressVerificationStatus::verified()->value,
        ]);
    }

    public function testCaseStatusUpdateTimeoutIndexStatus(): void
    {
        $eloquentCase = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'pairing_expires_at' => $this->faker->dateTimeThisMonth,
            'index_status' => IndexStatus::initial(),
        ]);
        $caseService = $this->app->get(CaseService::class);

        $dbCaseStatusRepository = $this->app->get(CaseStatusRepository::class);
        $dbCaseStatusRepository->updateTimeoutIndexStatus(100);

        $timeoutCase = $caseService->getCovidCaseFromEloquentModel($eloquentCase->refresh());
        $this->assertSame(BCOStatus::open(), $timeoutCase->bcoStatus);
        $this->assertSame(IndexStatus::timeout(), $timeoutCase->indexStatus);
    }

    public function testCaseStatusUpdateExpiresIndexStatus(): void
    {
        $eloquentCase = $this->createCase([
            'bco_status' => BCOStatus::open(),
            'index_status' => IndexStatus::paired(),
            'index_submitted_at' => null,
            'window_expires_at' => $this->faker->dateTimeThisMonth,
        ]);
        $caseService = $this->app->get(CaseService::class);

        $dbCaseStatusRepository = $this->app->get(CaseStatusRepository::class);
        $caseUuids = $dbCaseStatusRepository->updateExpiredIndexStatus(100);

        $this->assertEquals(collect([$eloquentCase->uuid]), $caseUuids);

        $expiredCase = $caseService->getCovidCaseFromEloquentModel($eloquentCase->refresh());
        $this->assertSame(BCOStatus::open(), $expiredCase->bcoStatus);
        $this->assertSame(IndexStatus::expired(), $expiredCase->indexStatus);
    }

    public function testCreateCaseCreatesAssigment(): void
    {
        $user = $this->createUser();
        $this->be($user);
        $caseService = $this->app->get(CaseService::class);
        $case = $caseService->createCase($this->emptyCaseData(), true);

        $this->assertDatabaseHas(CaseAssignmentHistory::class, [
            'assigned_user_uuid' => $user->uuid,
            'covidcase_uuid' => $case->uuid,
        ]);
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testCreateCaseShouldSendInitialMessageToOsiris(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        Queue::fake();

        $user = $this->createUser();
        $this->be($user);
        $caseService = $this->app->get(CaseService::class);
        $plannerCase = new PlannerCase();
        $plannerCase->index = Index::newInstanceWithVersion(1, function (Index $index): void {
            $index->firstname = $this->faker->firstName();
            $index->lastname = $this->faker->lastName();
            $index->dateOfBirth = $this->faker->dateTime();
        });
        $plannerCase->contact = Contact::newInstanceWithVersion(1, function (Contact $contact): void {
            $contact->phone = $this->faker->phoneNumber;
        });

        $case = $caseService->createPlannerCase($plannerCase, true);

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($case) {
            return $job->caseUuid === $case->uuid && $job->caseExportType === CaseExportType::INITIAL_ANSWERS;
        });

        ConfigHelper::disableFeatureFlag('osiris_send_case_enabled');
    }

    public function testCaseQualityFeedbackCaseLogsToCaseAssignmentHistory(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation);
        $this->be($user);

        $history = CaseAssignmentHistory::where('covidcase_uuid', $case->uuid);
        $this->assertSame(0, $history->count());

        $case->assigned_user_uuid = $user->uuid;
        $case->bcoStatus = BCOStatus::draft();
        $this->caseService->updateContactStatus(
            $case,
            ContactTracingStatus::conversationStarted(),
            null,
            CasequalityFeedback::rejectAndReopen(),
            null,
        );

        $this->assertSame(1, $history->count());
    }

    public function testFinishingACaseLogsToCaseAssignmentHistory(): void
    {
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation);
        $this->be($user);

        $history = CaseAssignmentHistory::where('covidcase_uuid', $case->uuid);
        $this->assertSame(0, $history->count());

        $case->assigned_user_uuid = $user->uuid;
        $case->bcoStatus = BCOStatus::draft();
        $this->caseService->updateContactStatus(
            $case,
            ContactTracingStatus::conversationStarted(),
            null,
            null,
            null,
        );

        $this->assertSame(1, $history->count());
    }

    #[DataProvider('searchByIdentifierProvider')]
    public function testFindCasesByIdentifierFindsDeletedCases(string $identifier): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'case_id' => 'AB1-123-123',
            'hpzone_number' => '1234322',
            'test_monster_number' => '123A4444444',
        ]);

        $case->delete();

        $this->assertSame(
            1,
            $this->caseService->findCasesByIdentifierForOwningOrganisation(new CaseIdentifier($identifier), $organisation->uuid)->count(),
        );
    }

    public static function searchByIdentifierProvider(): array
    {
        return [
            ['1234322'],
            ['123A4444444'],
            ['AB1-123-123'],
        ];
    }

    public function testFindCasesByIdentifierDoesNotFindCasesByOtherOrganisation(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation, [
            'case_id' => 'AB1-123-123',
            'hpzone_number' => '1234322',
            'test_monster_number' => '123A4444444',
        ]);

        $this->assertSame(
            1,
            $this->caseService->findCasesByIdentifierForOwningOrganisation(new CaseIdentifier($case->caseId), $organisation->uuid)->count(),
        );
        $this->assertSame(
            0,
            $this->caseService->findCasesByIdentifierForOwningOrganisation(new CaseIdentifier($case->caseId), '123')->count(),
        );
    }

    /**
     * @throws AuthenticationException
     */
    public function testUpdateOrganisationCannotBeDoneFromOutsourcedOrganisation(): void
    {
        $initialOrganisation = $this->createOrganisation();
        $assignedOrganisation = $this->createOrganisation();
        $updateOrganisation = $this->createOrganisation();

        $this->be($this->createUserForOrganisation($assignedOrganisation));

        $eloquentCase = $this->createCaseForOrganisation($initialOrganisation, [
            'assigned_organisation_uuid' => $assignedOrganisation->uuid,
        ]);

        $this->expectExceptionObject(new UpdateOrganisationUnauthorizedException());
        $this->caseService->updateCaseOrganisation($eloquentCase, $updateOrganisation->uuid);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $eloquentCase->uuid,
            'organisation_uuid' => $initialOrganisation->uuid,
            'assigned_organisation_uuid' => $assignedOrganisation->uuid,
        ]);
    }

    /**
     * @throws AuthenticationException
     */
    public function testUpdateOrganisationOnCaseWillRemoveAssignmentsFromChores(): void
    {
        $initialOrganisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($initialOrganisation));

        $eloquentCase = $this->createCaseForOrganisation($initialOrganisation);
        $chore = $this->createChoreForCaseAndOrganisation($eloquentCase, $initialOrganisation);
        $assignment = $this->createAssignmentForChore($chore);
        $updateOrganisation = $this->createOrganisation();

        $this->caseService->updateCaseOrganisation($eloquentCase, $updateOrganisation->uuid);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $eloquentCase->uuid,
        ]);

        $this->assertDatabaseHas('chore', [
            'uuid' => $chore->uuid,
        ]);

        $this->assertSoftDeleted('assignment', ['uuid' => $assignment->uuid]);
    }

    /**
     * @throws AuthenticationException
     */
    public function testUpdateOrganisationOnCaseWillChangeOrganisationFromChores(): void
    {
        $initialOrganisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($initialOrganisation));

        $eloquentCase = $this->createCaseForOrganisation($initialOrganisation);
        $chore = $this->createChoreForCaseAndOrganisation($eloquentCase, $initialOrganisation);
        $updateOrganisation = $this->createOrganisation();

        $this->caseService->updateCaseOrganisation($eloquentCase, $updateOrganisation->uuid);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $eloquentCase->uuid,
            'organisation_uuid' => $updateOrganisation->uuid,
        ]);

        $this->assertDatabaseHas('chore', [
            'uuid' => $chore->uuid,
            'organisation_uuid' => $updateOrganisation->uuid,
        ]);
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testCompletingCaseShouldCreateAJobOnOsirisQueue(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');
        Queue::fake();

        $createdAt = $this->faker->dateTimeBetween('-20 days', '-4 days');
        $updatedAt = CarbonImmutable::now();
        $notifiedAt = $this->faker->dateTimeBetween($createdAt, $updatedAt);
        $organisation = $this->createOrganisation();
        $user = $this->createUserForOrganisation($organisation);
        $case = $this->createCaseForOrganisation($organisation, [
            'created_at' => $createdAt,
            'updated_at' => $updatedAt,
            'index' => Index::newInstanceWithVersion(1, function (Index $index): void {
                $index->dateOfBirth = $this->faker->dateTime();
            }),
        ]);
        $this->createOsirisNotificationForCase($case, [
            'notified_at' => $notifiedAt,
        ]);
        $this->be($user);

        $case->assigned_user_uuid = $user->uuid;
        $case->bcoStatus = BCOStatus::draft();
        $this->caseService->updateContactStatus(
            $case,
            ContactTracingStatus::bcoFinished(),
            null,
            null,
            null,
        );

        Queue::assertPushed(static function (ExportCaseToOsiris $job) use ($case) {
            return $job->caseUuid === $case->uuid && $job->caseExportType === CaseExportType::DEFINITIVE_ANSWERS;
        });
    }

    public function testOpenCase(): void
    {
        $case = $this->createCase([
            'createdAt' => $this->faker->dateTimeBetween('-2 weeks', '-2 days'),
            'bcoStatus' => BCOStatus::draft(),
        ]);

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);

        $caseService->openCase($case);
        $this->assertEquals($case->bcoStatus, BCOStatus::open());
    }

    public function testOrganisationUuidIsRegisteredInEvent(): void
    {
        $organisation = $this->createOrganisation();
        $case = $this->createCaseForOrganisation($organisation);

        $this->caseService->openCase($case);

        $this->assertDatabaseHas('event', [
            'type' => 'opened',
            'data' => $this->castAsJson([
                'actor' => 'staff',
                'caseUuid' => $case->uuid,
            ]),
            'organisation_uuid' => $organisation->uuid,
        ]);
    }

    public function testAgeIsUpdatedWhenCaseIsCreated(): void
    {
        $index = $this->makeIndexInstance([
            'dateOfBirth' => CarbonImmutable::parse($this->faker->date()),
        ]);

        $case = $this->createCase([
            'index' => $index,
            'bco_status' => BCOStatus::open(),
        ]);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'index_age' => CarbonImmutable::now()->diffInYears($index->dateOfBirth),
        ]);
    }

    public function testAgeIsNotUpdatedWhenOnlyBcoPhaseChanges(): void
    {
        $case = $this->createCase([
            'index' => $this->makeIndexInstance([
                'dateOfBirth' => CarbonImmutable::parse($this->faker->date),
            ]),
            'bco_status' => BCOStatus::completed(),
        ]);

        $case->index_age = null;
        $case->saveQuietly();

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'index_age' => null,
        ]);

        $case->bcoStatus = BCOStatus::open();
        $case->save();

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'index_age' => null,
        ]);
    }

    public function testAgeIsUpdatedWhenDateOfBirthChanges(): void
    {
        $index = $this->makeIndexInstance([
            'dateOfBirth' => CarbonImmutable::parse($this->faker->date()),
        ]);

        $case = $this->createCase([
            'index' => $index,
            'bco_status' => BCOStatus::completed(),
        ]);

        $newDateOfBirth = CarbonImmutable::parse($this->faker->date());

        $case->index->dateOfBirth = $newDateOfBirth;
        $case->index->save();

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'index_age' => CarbonImmutable::now()->diffInYears($newDateOfBirth),
        ]);
    }

    #[Group('issue-BOOST-142')]
    public function testReturningCaseDoesNotAddCaseReopendToTimeline(): void
    {
        $user = $this->createUserWithOrganisation(roles: 'user');
        $case = $this->createCaseForUser($user, ['bco_status' => BCOStatus::open()]);

        $timeline = $case->timeline()->get();

        $this->assertCount(0, $timeline, 'Expected an empty timeline');

        $this->be($user);

        $this->caseService->updateContactStatus(
            $case,
            ContactTracingStatus::notStarted(),
            forceOsirisNotification: null,
            casequalityFeedback: null,
            statusExplanation: null,
        );

        $casesReopend = $case
            ->timeline()
            ->where('timelineable_type', 'note')
            ->with('timelineable')
            ->get()
            ->filter(static fn (Timeline $timeline): bool => $timeline->timelineable?->type === CaseNoteType::caseReopened());

        $this->assertCount(0, $casesReopend, 'Expected no case reopend notes!');
    }

    private function makeIndexInstance(array $array): Index
    {
        $indexSchemaVersion = EloquentCase::getSchema()
            ->getCurrentVersion()
            ->getField('index')
            ->getExpectedType(SchemaType::class)
            ->getSchemaVersion()
            ->getVersion();

        return Index::newInstanceWithVersion($indexSchemaVersion, static function (Index $index) use ($array): void {
            foreach ($array as $key => $value) {
                $index->$key = $value;
            }
        });
    }

    #[Group('osiris')]
    #[Group('osiris-case-export')]
    public function testUpdateArchivedCaseShouldSendMessageToOsiris(): void
    {
        ConfigHelper::enableFeatureFlag('osiris_send_case_enabled');

        $case = $this->createCase(['bcoStatus' => BCOStatus::archived()]);

        $this->be($this->createUserForOrganisation($case->organisation));
        $plannerCase = $this->caseService->getPlannerCase($case->uuid);

        Queue::fake();
        $this->caseService->updatePlannerCase($plannerCase);
        Queue::assertPushed(ExportCaseToOsiris::class, static function (ExportCaseToOsiris $job) use ($case) {
            return $job->caseUuid === $case->uuid && $job->caseExportType === CaseExportType::DEFINITIVE_ANSWERS;
        });

        ConfigHelper::disableFeatureFlag('osiris_send_case_enabled');
    }

    public function testCreatePlannerCaseSetsAutomaticAddressVerificationStatusToVerified(): void
    {
        $this->be($this->createUserWithOrganisation(roles: 'planner'));

        $mockPlannerCase = new PlannerCase();
        $mockPlannerCase->pseudoBsnGuid = $this->faker->uuid();

        $this->mock(BsnService::class, function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')
                ->andReturn(new PseudoBsn($this->faker->uuid(), $this->faker->numerify('******###'), $this->faker->randomLetter()));
        });

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);
        $case = $caseService->createPlannerCase($mockPlannerCase);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'automatic_address_verification_status' => AutomaticAddressVerificationStatus::verified()->value,
        ]);
    }

    public function testCreatePlannerCaseSetsAutomaticAddressVerificationStatusToUnverifiedIfBsnServiceLookupFails(): void
    {
        $this->be($this->createUserWithOrganisation(roles: 'planner'));

        $mockPlannerCase = new PlannerCase();
        $mockPlannerCase->pseudoBsnGuid = $this->faker->uuid();

        $this->mock(BsnService::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')->andThrows(BsnServiceException::class);
        });

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);
        $case = $caseService->createPlannerCase($mockPlannerCase);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'automatic_address_verification_status' => AutomaticAddressVerificationStatus::unverified()->value,
        ]);
    }

    public function testCreatePlannerCaseSetsAutomaticAddressVerificationStatusToUnverifiedIfPseudoBsnGuidNotSet(): void
    {
        $this->be($this->createUserWithOrganisation(roles: 'planner'));

        $mockPlannerCase = new PlannerCase();
        $mockPlannerCase->pseudoBsnGuid = null;

        $this->mock(BsnService::class, static function (MockInterface $mock): void {
            $mock->expects('getByPseudoBsnGuid')->never();
        });

        /** @var CaseService $caseService */
        $caseService = app(CaseService::class);
        $case = $caseService->createPlannerCase($mockPlannerCase);

        $this->assertDatabaseHas('covidcase', [
            'uuid' => $case->uuid,
            'automatic_address_verification_status' => AutomaticAddressVerificationStatus::unverified()->value,
        ]);
    }
}
