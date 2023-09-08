<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Eloquent\EloquentCase;
use App\Models\Task\General;
use App\Models\Task\Test;
use App\Repositories\CaseFragmentRepository;
use App\Repositories\ContextFragmentRepository;
use App\Repositories\TaskFragmentRepository;
use App\Services\FragmentMigrationService;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Encryption\Security\StorageTerm;
use MinVWS\DBCO\Enum\Models\YesNoUnknown;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

use function app;

#[Group('fragment-migration')]
class FragmentMigrationServiceTest extends FeatureTestCase
{
    private FragmentMigrationService $fragmentMigrationService;
    private CaseFragmentRepository $caseFragmentRepository;
    private TaskFragmentRepository $taskFragmentRepository;
    private ContextFragmentRepository $contextFragmentRepository;
    private EloquentCase $case;

    protected function setUp(): void
    {
        parent::setUp();

        $this->fragmentMigrationService = app(FragmentMigrationService::class);
        $this->caseFragmentRepository = app(CaseFragmentRepository::class);
        $this->taskFragmentRepository = app(TaskFragmentRepository::class);
        $this->contextFragmentRepository = app(ContextFragmentRepository::class);

        $user = $this->createUser();
        $this->case = $this->createCaseForUser($user, ['created_at' => CarbonImmutable::now()]);
    }

    public function testFragmentMigrationCovidCase(): void
    {
        $general = $this->createSealedStoreValue(StorageTerm::short(), CarbonImmutable::now(), [
            'notes' => 'Lorem Ipsum',
        ]);

        DB::table('covidcase')
            ->where('uuid', $this->case->uuid)
            ->update(['general' => $general]);

        $this->fragmentMigrationService->covidCase(['general'])
            ->update(static function (object $case, array $fragments): void {
                $fragments['general']->notes = 'My new note';
            });

        $fragments = $this->caseFragmentRepository->loadCaseFragments($this->case->uuid, ['general']);

        $this->assertEquals('My new note', $fragments['general']->notes);
    }

    public function testFragmentMigrationCovidCaseUpdateResult(): void
    {
        $general = $this->createSealedStoreValue(StorageTerm::short(), CarbonImmutable::now(), [
            'notes' => 'Lorem Ipsum',
        ]);

        DB::table('covidcase')
            ->where('uuid', $this->case->uuid)
            ->update(['general' => $general]);

        $result = $this->fragmentMigrationService->covidCase(['general'])
            ->update(static function (object $case, array $fragments) {
                $fragments['general']->notes = 'My note 1';
                return true;
            });

        $this->assertEquals(1, $result->getUpdatedCount());
        $this->assertEquals(0, $result->getSkippedCount());

        $result = $this->fragmentMigrationService->covidCase(['general'])
            ->update(static function (object $case, array $fragments) {
                $fragments['general']->notes = 'My note 2';
                return false;
            });

        $this->assertEquals(0, $result->getUpdatedCount());
        $this->assertEquals(1, $result->getSkippedCount());

        $result = $this->fragmentMigrationService->covidCase(['general'])
            ->update(static function (object $case, array $fragments): void {
                $fragments['general']->notes = 'My note 3';
                // no return, assume update
            });

        $this->assertEquals(1, $result->getUpdatedCount());
        $this->assertEquals(0, $result->getSkippedCount());
    }

    public function testFragmentMigrationTask(): void
    {
        $task = $this->createTaskForCase($this->case, [
            'created_at' => CarbonImmutable::now(),
            'general' => General::newInstanceWithVersion(1, static function (General $general): void {
                $general->firstname = 'Ralph';
                $general->lastname = 'Niels';
            }),
            'test' => Test::newInstanceWithVersion(1, static function (Test $test): void {
                $test->isTested = YesNoUnknown::yes();
            }),
        ]);

        $this->fragmentMigrationService->task(['general', 'test'])
            ->update(static function (object $task, array $fragments): void {
                $lastname = $fragments['general']->lastname;
                $fragments['general']->lastname = $fragments['general']->firstname;
                $fragments['general']->firstname = $lastname;
                $fragments['test']->isTested = YesNoUnknown::yes()->value;
            });

        $fragments = $this->taskFragmentRepository->loadTaskFragments($task->uuid, ['general', 'test']);

        $this->assertEquals('Niels', $fragments['general']->firstname);
        $this->assertEquals('Ralph', $fragments['general']->lastname);
        $this->assertEquals(YesNoUnknown::yes(), $fragments['test']->isTested);
    }

    public function testFragmentMigrationContext(): void
    {
        $context = $this->createContextForCase($this->case, ['created_at' => CarbonImmutable::now()]);
        $context->circumstances->isUsingPPE = YesNoUnknown::no();
        $context->contact->phone = '0612345678';
        $context->save();

        $this->fragmentMigrationService->context(['contact', 'circumstances'])
            ->update(static function (object $context, array $fragments): void {
                $fragments['contact']->phone = '0687654321';
                $fragments['circumstances']->isUsingPPE = YesNoUnknown::yes()->value;
            });

        $fragments = $this->contextFragmentRepository->loadContextFragments($context->uuid, ['contact', 'circumstances']);

        $this->assertEquals('06 87654321', $fragments['contact']->phone);
        $this->assertEquals(YesNoUnknown::yes(), $fragments['circumstances']->isUsingPPE);
    }
}
