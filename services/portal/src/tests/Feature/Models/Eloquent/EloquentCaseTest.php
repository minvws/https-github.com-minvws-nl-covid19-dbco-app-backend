<?php

declare(strict_types=1);

namespace Tests\Feature\Models\Eloquent;

use App\Jobs\ContactSearchHashJob;
use App\Jobs\IndexSearchHashJob;
use App\Models\CovidCase\Index;
use App\Models\Eloquent\CaseAssignmentHistory;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentAnswer;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use Carbon\CarbonImmutable;
use DateTime;
use Generator;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\BCOStatus;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\Feature\FeatureTestCase;

use function json_decode;
use function mb_strtoupper;
use function sprintf;
use function strlen;

#[Group('case')]
class EloquentCaseTest extends FeatureTestCase
{
    #[Group('search-hash')]
    public function testCreatingCaseWithFragmentsDispatchesSearchHashJobs(): void
    {
        Bus::fake();

        $case = EloquentCase::factory()->withFragments()->create();

        Bus::assertDispatched(static fn (ContactSearchHashJob $job): bool => $job->caseUuid === $case->uuid);
        Bus::assertDispatched(static fn (IndexSearchHashJob $job): bool => $job->caseUuid === $case->uuid);
    }

    public function testCaseIdIsGeneratedIfNotSet(): void
    {
        $case = $this->createCase();

        $this->assertSame(11, strlen($case->caseId));
    }

    public function testNameAttributeWithoutInitials(): void
    {
        $case = $this->createCase([
            'index' => Index::newInstanceWithVersion(Index::getSchema()->getMaxVersion()->getVersion(), function (Index $index): void {
                $index->firstname = $this->faker->firstname();
                $index->lastname = $this->faker->lastname();
            }),
        ]);

        $this->assertSame("{$case->index->firstname} {$case->index->lastname}", $case->getNameAttribute());
    }

    public function testNameAttributeShowsInitialsWithBracketsIfFullName(): void
    {
        $case = $this->createCase([
            'index' => Index::newInstanceWithVersion(Index::getSchema()->getMaxVersion()->getVersion(), function (Index $index): void {
                $index->firstname = $this->faker->firstname();
                $index->initials = $this->faker->suffix();
                $index->lastname = $this->faker->lastname();
            }),
        ]);

        $initialsUpper = mb_strtoupper($case->index->initials);
        $this->assertSame("{$case->index->firstname} ({$initialsUpper}) {$case->index->lastname}", $case->getNameAttribute());
    }

    public function testNameAttributeShowsInitialsWithoutBracketsIfNoFirstname(): void
    {
        $case = $this->createCase([
            'index' => Index::newInstanceWithVersion(Index::getSchema()->getMaxVersion()->getVersion(), function (Index $index): void {
                $index->initials = $this->faker->suffix();
                $index->lastname = $this->faker->lastname();
            }),
        ]);

        $initialsUpper = mb_strtoupper($case->index->initials);
        $this->assertSame("{$initialsUpper} {$case->index->lastname}", $case->getNameAttribute());
    }

    public function testCaseIdIsNotRegeneratedIfSet(): void
    {
        $case = $this->createCase();

        $oldCaseId = $case->caseId;

        $case = EloquentCase::find($case->uuid);
        $case->hpzoneNumber = '1231231';
        $case->save();

        $this->assertSame($oldCaseId, $case->caseId);
    }

    public function testCaseIdIsNotRegeneratedOnPartialFetchAndSave(): void
    {
        $case = $this->createCase();

        $oldCaseId = $case->caseId;

        /** @var EloquentCase $case */
        $case = EloquentCase::find($case->uuid, ['uuid', 'bco_status']);
        $case->bcoStatus = BCOStatus::completed();
        $case->save();

        $this->assertSame($oldCaseId, EloquentCase::find($case->uuid)->caseId);
    }

    public function testChangeCaseIdIsNotAllowed(): void
    {
        $case = $this->createCase([
            'case_id' => '123',
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Overwriting case_id is not allowed for case');
        $case->caseId = '456';
    }

    public function testCaseIdIsChangedIfSameAsHpzoneNumber(): void
    {
        $case = $this->createCase([
            'case_id' => '1234567',
            'hpzone_number' => '1234567',
        ]);

        $case->hpzoneNumber = '1111111';
        $case->save();

        $this->assertSame($case->caseId, '1111111');
    }

    public function testCaseIdIsNotChangedIfDifferentFromHpzoneNumber(): void
    {
        $case = $this->createCase([
            'case_id' => 'AA1-123-123',
            'hpzone_number' => '1234567',
        ]);

        $case->hpzoneNumber = '1111111';
        $case->save();

        $this->assertSame($case->caseId, 'AA1-123-123');
        $this->assertSame($case->hpzoneNumber, '1111111');
    }

    public function testCaseCanBeAssigned(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));

        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::open(),
            'assigned_organisation_uuid' => null,
        ]);

        $this->assertTrue($case->isAssignable());
    }

    public function testArchivedCaseCannotBeAssigned(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));

        $case = $this->createCaseForOrganisation($organisation, [
            'bco_status' => BCOStatus::archived(),
            'assigned_organisation_uuid' => null,
        ]);

        $this->assertFalse($case->isAssignable());
    }

    public function testCaseCanNotBeAssignedWhenBcoStatusIsArchived(): void
    {
        $organisation = $this->createOrganisation();
        $this->be($this->createUserForOrganisation($organisation));
        $case = $this->createCaseForOrganisation($organisation, ['bco_status' => BCOStatus::archived()]);

        $this->assertFalse($case->isAssignable());
    }

    public function testDataIsEncryptedJsonWithCorrectPrefix(): void
    {
        $indexSubmittedSymptoms = $this->faker->word();

        $eloquentCase = $this->createCase([
            'created_at' => CarbonImmutable::now(),
            'index_submitted_symptoms' => $indexSubmittedSymptoms,
        ]);

        $databaseResult = DB::table('covidcase')
            ->select('index_submitted_symptoms')
            ->where('uuid', $eloquentCase->uuid)
            ->first();
        $this->assertEquals(
            sprintf('store:%s', CarbonImmutable::now()->format('Ym')),
            json_decode($databaseResult->index_submitted_symptoms)->key,
        );
    }

    public function testDelete(): void
    {
        /** @var EloquentCase $case */
        $case = EloquentCase::factory()->create();
        $this->assertNotNull($case);

        $context = Context::factory()->create(['covidcase_uuid' => $case->uuid]);
        $this->assertNotNull($context);
        $this->assertEquals($case->uuid, $context->covidcase_uuid);

        $task = EloquentTask::factory()->create(['case_uuid' => $case->uuid]);
        $this->assertNotNull($task);
        $this->assertEquals($case->uuid, $task->case_uuid);

        $assignmentHistory = new CaseAssignmentHistory();
        $assignmentHistory->covidcase_uuid = $case->uuid;
        $assignmentHistory->assigned_at = new DateTime();
        $assignmentHistory->save();
        $this->assertNotNull($assignmentHistory);
        $this->assertEquals($case->uuid, $assignmentHistory->covidcase_uuid);

        $answer = EloquentAnswer::factory()->create(['task_uuid' => $task->uuid]);
        $this->assertNotNull($answer);
        $this->assertEquals($task->uuid, $answer->task_uuid);

        $case->delete();

        $this->assertNull(EloquentCase::find($case->uuid));
        $this->assertNotNull(EloquentCase::withTrashed()->find($case->uuid));

        $case->forceDelete();

        $this->assertNull(EloquentCase::find($case->uuid));
        $this->assertNull(EloquentCase::withTrashed()->find($case->uuid));
    }

    #[DataProvider('getVersionedResourceTypeProvider')]
    public function testGetVersionedResourceType(
        string $expectedVersionedResourceType,
        int $version,
    ): void {
        $eloquentCase = $this->createCase(['schema_version' => $version]);

        self::assertEquals($expectedVersionedResourceType, $eloquentCase->getVersionedResourceType());
    }

    public static function getVersionedResourceTypeProvider(): Generator
    {
        foreach (EloquentCase::getSchema()->getVersions() as $version) {
            yield "covid-case-v$version" => ["covid-case-v$version", $version];
        }
    }
}
