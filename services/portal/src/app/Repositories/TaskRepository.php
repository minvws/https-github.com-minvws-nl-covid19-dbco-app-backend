<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\TaskGroup;

interface TaskRepository
{
    /**
     * @return Collection<int, Task>
     */
    public function getTasks(string $caseUuid, TaskGroup $group): Collection;

    public function getTask(string $taskUuid): ?Task;

    public function getEloquentTask(string $taskUuid): ?EloquentTask;

    public function getTaskByUuid(string $taskUuid): ?EloquentTask;

    public function getTaskIncludingSoftDeletes(string $taskUuid): ?EloquentTask;

    public function getTaskModelIncludingSoftDeletes(string $taskUuid): ?Task;

    public function restoreTask(EloquentTask $task): void;

    public function updateTask(Task $task): bool;

    public function createTask(
        string $caseUuid,
        TaskGroup $group,
        string $label,
        ?string $context,
        ?string $nature,
        ?string $category,
        ?string $communication,
        ?CarbonInterface $dateOfLastExposure,
        bool $isSource,
    ): Task;

    public function deleteTask(EloquentTask $task): void;

    /**
     * @param array<string, mixed> $conditions
     *
     * @return Collection<int, EloquentTask>
     */
    public function searchTasksForOrganisation(array $conditions, string $organisationUuid): Collection;

    public function getTasksByPseudoBsnGuid(string $pseudoBsnGuid, array $ignoreUuids = []): Collection;

    public function createTaskForCase(EloquentCase $case): EloquentTask;

    public function save(EloquentTask $task): void;

    public function countTaskGroupsForCase(EloquentCase $case): array;
}
