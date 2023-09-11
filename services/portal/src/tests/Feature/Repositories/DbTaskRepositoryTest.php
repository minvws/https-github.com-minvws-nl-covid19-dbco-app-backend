<?php

declare(strict_types=1);

namespace Tests\Feature\Repositories;

use App\Models\Eloquent\EloquentTask;
use App\Repositories\DbTaskRepository;
use Carbon\CarbonImmutable;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use PHPUnit\Framework\Attributes\Group;
use Tests\Feature\FeatureTestCase;

#[Group('task')]
class DbTaskRepositoryTest extends FeatureTestCase
{
    public function testCreateTaskMinimalData(): void
    {
        $case = $this->createCase();

        $dbTaskRepository = $this->app->get(DbTaskRepository::class);
        $task = $dbTaskRepository->createTask(
            $case->uuid,
            TaskGroup::contact(),
            'label',
            null,
            null,
            null,
            null,
            null,
            false,
        );

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'task_group' => TaskGroup::contact()->value,
            'is_source' => 0,
        ]);

        $this->assertEquals('label', $task->label);
    }

    public function testCreateTaskFullData(): void
    {
        $case = $this->createCase();

        $dbTaskRepository = $this->app->get(DbTaskRepository::class);
        $task = $dbTaskRepository->createTask(
            $case->uuid,
            TaskGroup::positiveSource(),
            'label',
            'context',
            'nature',
            ContactCategory::cat1()->value,
            InformedBy::index()->value,
            CarbonImmutable::createFromDate(2020, 1, 1),
            false,
        );

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'task_group' => TaskGroup::positiveSource()->value,
            'nature' => 'nature',
            'category' => '1',
            'communication' => 'index',
            'date_of_last_exposure' => '2020-01-01',
            'is_source' => 0,
        ]);

        $this->assertEquals('label', $task->label);
        $this->assertEquals('context', $task->taskContext);
    }

    public function testCreateTaskEmptyStringForCategoryShouldSucceed(): void
    {
        $case = $this->createCase();

        $dbTaskRepository = $this->app->get(DbTaskRepository::class);

        $task = $dbTaskRepository->createTask(
            $case->uuid,
            TaskGroup::contact(),
            'label',
            null,
            null,
            '',
            null,
            null,
            false,
        );

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'category' => null,
            'task_group' => TaskGroup::contact()->value,
        ]);
    }

    public function testCreateTaskEmptyStringForCommunicationShouldSucceed(): void
    {
        $case = $this->createCase();

        $dbTaskRepository = $this->app->get(DbTaskRepository::class);

        $task = $dbTaskRepository->createTask(
            $case->uuid,
            TaskGroup::contact(),
            'label',
            null,
            null,
            null,
            '',
            null,
            false,
        );

        $this->assertDatabaseHas('task', [
            'uuid' => $task->uuid,
            'communication' => null,
            'task_group' => TaskGroup::contact()->value,
        ]);
    }

    public function testCreateTaskInvalidCategoryShouldFail(): void
    {
        $case = $this->createCase();

        $dbTaskRepository = $this->app->get(DbTaskRepository::class);

        $this->expectException(InvalidArgumentException::class);
        $dbTaskRepository->createTask(
            $case->uuid,
            TaskGroup::contact(),
            'label',
            null,
            null,
            'foo',
            null,
            null,
            false,
        );
    }

    public function testCreateTaskInvalidCommunicationShouldFail(): void
    {
        $case = $this->createCase();

        $dbTaskRepository = $this->app->get(DbTaskRepository::class);

        $this->expectException(InvalidArgumentException::class);
        $dbTaskRepository->createTask(
            $case->uuid,
            TaskGroup::contact(),
            'label',
            null,
            null,
            null,
            'foo',
            null,
            false,
        );
    }

    public function testTaskCRUD(): void
    {
        $dbTaskRepository = $this->app->get(DbTaskRepository::class);
        $eloquentCase = $this->createCase();

        $task = $dbTaskRepository->createTask(
            $eloquentCase->uuid,
            TaskGroup::contact(),
            'PHPUnit Task 1',
            'context',
            null,
            '1',
            'staff',
            null,
            false,
        );

        // Basic create-retrieve roundtrip
        $retrievedTask = $dbTaskRepository->getTask($task->uuid);
        $this->assertNotSame($task, $retrievedTask);
        $this->assertEquals($task, $retrievedTask);
        $this->assertNull($task->questionnaireUuid, "Newly created Tasks should not be associated with a questionnaire");

        // Update
        $task->label = "PHPUnit Edited Task";
        $task->taskContext = "edited context";
        $task->category = "3a";
        $task->communication = InformedBy::index()->value;
        $task->questionnaireUuid = "00000000-0000-0000-0000-000000000001";
        $dbTaskRepository->updateTask($task);

        $retrievedTask = $dbTaskRepository->getTask($task->uuid);
        // The updateAt field is updated in Mysql through its internal triggers. Depending when the test is run
        // this could lead to a time difference of a second and causing a failed test. Aligning the
        // updated at prevents this.
        $task->updatedAt = $retrievedTask->updatedAt;
        $this->assertNotSame($task, $retrievedTask);
        $this->assertEquals($task, $retrievedTask);

        // Delete
        $eloquentTask = $dbTaskRepository->getTaskByUuid($task->uuid);
        $dbTaskRepository->deleteTask($eloquentTask);
        $this->assertNull($dbTaskRepository->getTask($task->uuid));
        $this->assertEquals('deleted', EloquentTask::withTrashed()->find($task->uuid)->status); // check if soft-deleted
    }
}
