<?php

declare(strict_types=1);

namespace Tests\Feature\Services;

use App\Models\Eloquent\EloquentOrganisation;
use App\Models\Eloquent\EloquentUser;
use App\Services\CaseConnectedService;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('case-connected')]
class CaseConnectedServiceTest extends FeatureTestCase
{
    private EloquentOrganisation $organisation;
    private EloquentUser $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->organisation = $this->createOrganisation();
        $this->user = $this->createUserForOrganisation($this->organisation);

        $this->be($this->user);
    }

    public function testGetConnectedCasesAndTasksForCaseWithoutPseudoBsn(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForCase($case);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForCaseWithPseudoBsnButNonConnected(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, [
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForCase($case);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForCaseWithPseudoBsnOneCaseConnected(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, [
            'pseudo_bsn_guid' => 'foo',
        ]);
        $connectedCase = $this->createCaseForOrganisation($this->organisation, [
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForCase($case);

        $this->assertCount(1, $connectedCasesAndTasks['cases']);
        $this->assertEquals($connectedCase->uuid, $connectedCasesAndTasks['cases']->first()['uuid']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForTaskWithoutPseudoBsn(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::yesterday(),
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForTask($task);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForTaskWithPseudoBsnButNonConnected(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation);
        $task = $this->createTaskForCase($case, [
            'created_at' => CarbonImmutable::yesterday(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForTask($task);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForTaskWithPseudoBsnOneTaskConnected(): void
    {
        $case1 = $this->createCaseForOrganisation($this->organisation);
        $task = $this->createTaskForCase($case1, [
            'created_at' => CarbonImmutable::yesterday(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $case2 = $this->createCaseForOrganisation($this->organisation);
        $connectedTask = $this->createTaskForCase($case2, [
            'created_at' => CarbonImmutable::yesterday(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForTask($task);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertEquals($connectedTask->uuid, $connectedCasesAndTasks['tasks']->first()['uuid']);
        $this->assertCount(1, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForTaskWithPseudoBsnOneAnonimizedTaskConnected(): void
    {
        $case1 = $this->createCaseForOrganisation($this->organisation);
        $task = $this->createTaskForCase($case1, [
            'created_at' => CarbonImmutable::yesterday(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $case2 = $this->createCaseForOrganisation($this->organisation);
        $this->createTaskForCase($case2, [
            'created_at' => CarbonImmutable::now()->subYear(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForTask($task);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForCaseWithPseudoBsnOneCaseAndOneTaskConnected(): void
    {
        $case = $this->createCaseForOrganisation($this->organisation, [
            'pseudo_bsn_guid' => 'foo',
        ]);

        $this->createCaseForOrganisation($this->organisation, [
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseForTask = $this->createCaseForOrganisation($this->organisation);
        $this->createTaskForCase($caseForTask, [
            'created_at' => CarbonImmutable::yesterday(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForCase($case);

        $this->assertCount(1, $connectedCasesAndTasks['cases']);
        $this->assertCount(1, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForTaskWithPseudoBsnOneCaseAndOneTaskConnected(): void
    {
        $caseForTask1 = $this->createCaseForOrganisation($this->organisation);
        $task = $this->createTaskForCase($caseForTask1, [
            'pseudo_bsn_guid' => 'foo',
        ]);

        $this->createCaseForOrganisation($this->organisation, [
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseForTask2 = $this->createCaseForOrganisation($this->organisation);
        $this->createTaskForCase($caseForTask2, [
            'created_at' => CarbonImmutable::yesterday(),
            'pseudo_bsn_guid' => 'foo',
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForTask($task);

        $this->assertCount(1, $connectedCasesAndTasks['cases']);
        $this->assertCount(1, $connectedCasesAndTasks['tasks']);
    }

    public function testGetConnectedCasesAndTasksForTaskWithRelatedCaseBeingSoftDeleted(): void
    {
        $pseudoBsnGuid = $this->faker->word();

        $caseOne = $this->createCaseForOrganisation($this->organisation);
        $this->createTaskForCase($caseOne, [
            'pseudo_bsn_guid' => $pseudoBsnGuid,
        ]);

        $caseOne->delete();

        $caseTwo = $this->createCaseForOrganisation($this->organisation);
        $taskTwo = $this->createTaskForCase($caseTwo, [
            'pseudo_bsn_guid' => $pseudoBsnGuid,
        ]);

        $caseConnectedService = $this->app->get(CaseConnectedService::class);
        $connectedCasesAndTasks = $caseConnectedService->getConnectedCasesAndTasksForTask($taskTwo);

        $this->assertCount(0, $connectedCasesAndTasks['cases']);
        $this->assertCount(0, $connectedCasesAndTasks['tasks']);
    }
}
