<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentTask;
use App\Models\Task;
use App\Scopes\CaseAuthScope;
use App\Scopes\OrganisationAuthScope;
use App\Services\Task\TaskDecryptableDefiner;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use MinVWS\DBCO\Enum\Models\ContactCategory;
use MinVWS\DBCO\Enum\Models\InformedBy;
use MinVWS\DBCO\Enum\Models\InformStatus;
use MinVWS\DBCO\Enum\Models\TaskGroup;
use stdClass;

use function array_fill_keys;
use function array_filter;
use function array_merge;
use function count;

class DbTaskRepository implements TaskRepository
{
    public function __construct(
        private readonly TaskDecryptableDefiner $taskDecryptableDefiner,
    ) {
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasks(string $caseUuid, TaskGroup $group): Collection
    {
        /** @var EloquentCollection<int, EloquentTask> $dbTasks */
        $dbTasks = EloquentTask::with('covidcase')
            ->where('case_uuid', $caseUuid)
            ->where('task_group', $group->value)
            ->orderBy('task_group', 'asc')
            ->orderBy('created_at', 'desc')
            ->get();

        return $dbTasks
            ->collect()
            ->map(fn (EloquentTask $task): Task => $this->taskFromEloquentModel($task))
            ->values();
    }

    public function getTask(string $taskUuid): ?Task
    {
        $dbTask = $this->getTaskFromDb($taskUuid);

        return $dbTask !== null ? $this->taskFromEloquentModel($dbTask) : null;
    }

    public function getTaskByUuid(string $taskUuid): ?EloquentTask
    {
        return $this->getTaskFromDb($taskUuid);
    }

    public function getTaskIncludingSoftDeletes(string $taskUuid): ?EloquentTask
    {
        return EloquentTask::withTrashed()
            ->where('uuid', $taskUuid)
            ->first();
    }

    public function getTaskModelIncludingSoftDeletes(string $taskUuid): ?Task
    {
        $eloquentTask = EloquentTask::withTrashed()->where('uuid', $taskUuid)->first();

        if ($eloquentTask === null) {
            return null;
        }

        return $this->taskFromEloquentModel($eloquentTask);
    }

    public function restoreTask(EloquentTask $task): void
    {
        $task->restore();
    }

    public function getEloquentTask(string $taskUuid): ?EloquentTask
    {
        return $this->getTaskFromDb($taskUuid);
    }

    public function updateTask(Task $task): bool
    {
        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO fixme: this retrieves the object from the db, again; but eloquent won't let us easily instantiate
        // an object directly from a Task.
        $dbTask = $this->getTaskFromDb($task->uuid);

        if ($dbTask === null) {
            return false;
        }

        $dbTask->taskGroup = $task->group ?? $dbTask->taskGroup;
        $dbTask->label = $task->label;
        $dbTask->task_context = $task->taskContext;
        $dbTask->nature = $task->nature ?? '';
        $dbTask->communication = $this->getCommunicationValue($task->communication);
        $dbTask->informed_by_staff_at = $task->informedByStaffAt?->clone();
        $dbTask->category = $this->getCategoryValue($task->category);
        $dbTask->date_of_last_exposure = $task->dateOfLastExposure?->clone();
        $dbTask->export_id = $task->exportId;
        $dbTask->created_at = $task->createdAt;
        $dbTask->updated_at = $task->updatedAt;
        $dbTask->questionnaire_uuid = $task->questionnaireUuid;
        $dbTask->exported_at = $task->exportedAt?->clone();
        $dbTask->copied_at = $task->copiedAt?->clone();
        $dbTask->status = $task->status ?? Task::TASK_STATUS_OPEN;
        $dbTask->is_source = $task->isSource;
        $dbTask->dossier_number = $task->dossierNumber;
        $dbTask->inform_status = $task->informStatus;
        $dbTask->pseudo_bsn_guid = $task->pseudoBsnGuid;

        return $dbTask->save();
    }

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
    ): Task {
        /** @var EloquentCase $case */
        $case = EloquentCase::query()->find($caseUuid);
        if (!$case instanceof EloquentCase) {
            throw new InvalidArgumentException("Invalid case identifier \"{$caseUuid}\"!");
        }

        $dbTask = $case->createTask();
        $dbTask->created_at ??= CarbonImmutable::now();
        $dbTask->updated_at ??= $dbTask->created_at;
        $dbTask->taskGroup = $group;
        $dbTask->label = $label;
        $dbTask->task_context = $context;
        $dbTask->nature = $nature ?? '';
        $dbTask->category = $this->getCategoryValue($category);
        $dbTask->date_of_last_exposure = $dateOfLastExposure?->clone();
        $dbTask->communication = $this->getCommunicationValue($communication);
        $dbTask->source = 'portal';
        $dbTask->task_type = 'contact';
        $dbTask->is_source = $isSource;
        $dbTask->inform_status = InformStatus::defaultItem();

        $dbTask->save();
        return $this->taskFromEloquentModel($dbTask);
    }

    public function deleteTask(EloquentTask $task): void
    {
        $task->delete();
    }

    public function searchTasksForOrganisation(array $conditions, string $organisationUuid): Collection
    {
        $conditions = array_filter($conditions);

        if (empty($conditions)) {
            return Collection::make();
        }

        return EloquentTask::withTrashed()
            ->where(static function ($query) use ($conditions): void {
                foreach ($conditions as $column => $value) {
                    $query = $query->where($column, $value);
                }
            })
            ->whereHas('covidCase', static fn($query) => $query->where('organisation_uuid', $organisationUuid))
            ->get();
    }

    public function getTasksByPseudoBsnGuid(string $pseudoBsnGuid, array $ignoreUuids = []): Collection
    {
        return EloquentTask::where('pseudo_bsn_guid', $pseudoBsnGuid)
            ->with([
                'covidCase.organisation' => static function (Builder $query): void {
                    $query->withoutGlobalScope(OrganisationAuthScope::class);
                }])
            ->withWhereHas('covidCase', static function (Builder $query): void {
                $query->withoutGlobalScope(CaseAuthScope::class);
            })
            ->when(count($ignoreUuids) > 0, static function (Builder $query) use ($ignoreUuids): void {
                $query->whereNotIn('uuid', $ignoreUuids);
            })
            ->orderby('created_at', 'desc')
            ->get();
    }

    public function createTaskForCase(EloquentCase $case): EloquentTask
    {
        $task = $case->createTask();
        $task->created_at = CarbonImmutable::now();
        $task->updated_at = CarbonImmutable::now();
        $task->task_type = 'contact';

        return $task;
    }

    public function save(EloquentTask $task): void
    {
        $task->save();
    }

    private function getTaskFromDb(string $taskUuid): ?EloquentTask
    {
        return EloquentTask::query()
            ->where('uuid', $taskUuid)
            ->first();
    }

    private function taskFromEloquentModel(EloquentTask $dbTask): Task
    {
        $task = new Task();
        $task->uuid = $dbTask->uuid;
        $task->internalReference = $dbTask->internalReference();
        $task->group = $dbTask->taskGroup;
        $task->caseUuid = $dbTask->case_uuid;
        $task->category = (string) $dbTask->category;
        $task->communication = $dbTask->communication !== null ? $dbTask->communication->value : null;
        $task->dateOfLastExposure = $dbTask->date_of_last_exposure !== null ? new CarbonImmutable($dbTask->date_of_last_exposure) : null;
        $task->informedByIndexAt = $dbTask->informed_by_index_at;
        $task->informedByStaffAt = $dbTask->informed_by_staff_at !== null ? new CarbonImmutable($dbTask->informed_by_staff_at) : null;
        $task->exportedAt = $dbTask->exported_at;
        $task->copiedAt = $dbTask->copied_at !== null ? new CarbonImmutable($dbTask->copied_at) : null;
        $task->source = $dbTask->source;
        $task->nature = $dbTask->nature;
        $task->taskType = $dbTask->task_type;
        $task->questionnaireUuid = $dbTask->questionnaire_uuid;
        $task->exportId = $dbTask->export_id;
        $task->createdAt = $dbTask->created_at !== null ? new CarbonImmutable($dbTask->created_at) : null;
        $task->updatedAt = $dbTask->updated_at !== null ? new CarbonImmutable($dbTask->updated_at) : null;
        $task->deletedAt = $dbTask->deleted_at !== null ? new CarbonImmutable($dbTask->deleted_at) : null;
        $task->status = $dbTask->status ?? Task::TASK_STATUS_OPEN;
        $task->isSource = $dbTask->is_source;
        $task->dossierNumber = $dbTask->dossier_number;
        $task->informStatus = $dbTask->inform_status;
        $task->pseudoBsnGuid = $dbTask->pseudo_bsn_guid;

        if ($this->taskDecryptableDefiner->isDecryptable($task)) {
            $task->derivedLabel = $dbTask->derivedLabel;
            $task->label = $dbTask->label;
            $task->taskContext = $dbTask->task_context;
        }

        return $task;
    }

    private function getCommunicationValue(?string $communication): ?InformedBy
    {
        if ($communication === '' || $communication === null) {
            return null;
        }

        return InformedBy::from($communication);
    }

    private function getCategoryValue(?string $category): ?ContactCategory
    {
        if ($category === '' || $category === null) {
            return null;
        }

        return ContactCategory::from($category);
    }

    public function countTaskGroupsForCase(EloquentCase $case): array
    {
        /** @var Collection<string, stdClass> $result */
        $result = DB::table('task')
            ->selectRaw('task_group, count(1) as count')
            ->where('case_uuid', $case->uuid)
            ->whereNull('deleted_at')
            ->groupBy(['case_uuid', 'task_group'])
            ->get()
            ->keyBy('task_group');

        $onlyCounts = $result
            ->map(static fn ($row) => $row->count)
            ->toArray();

        return array_merge(array_fill_keys(TaskGroup::all(), 0), $onlyCounts);
    }
}
