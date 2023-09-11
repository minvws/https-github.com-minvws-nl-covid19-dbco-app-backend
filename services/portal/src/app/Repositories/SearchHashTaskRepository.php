<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\EloquentTask;
use App\Models\Eloquent\TaskSearch;
use App\Scopes\CaseAuthScope;
use App\Services\SearchHash\Dto\SearchHashResult;
use App\Services\SearchHash\Exception\SearchHashRuntimeException;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

use function array_search;
use function assert;
use function count;

final class SearchHashTaskRepository
{
    public function __construct(
        private readonly EloquentTask $eloquentTask,
        private readonly TaskSearch $taskSearch,
    ) {
    }

    /**
     * @param array<array-key,string> $relations
     */
    public function getTaskByUuid(string $taskUuid, array $relations = []): ?EloquentTask
    {
        /** @var EloquentTask|null $task */
        $task = $this->eloquentTask
            ->newQuery()
            ->with($relations)
            ->find($taskUuid);

        return $task;
    }

    /**
     * @param array<array-key,string> $taskUuids
     * @param array<array-key,string> $relations
     * @param array<array-key,string> $columns
     *
     * @return Collection<int,EloquentTask>
     */
    public function getTasksByUuids(
        array $taskUuids,
        string $organisationUuid,
        array $relations = [],
        array $columns = ['*'],
    ): Collection {
        assert(
            count($taskUuids) <= 1000,
            new SearchHashRuntimeException('Not allowed to pass more than a 1000 taskUuids!'),
        );

        $key = array_search('covidCase', $relations, true);
        if ($key !== false) {
            unset($relations[$key]);
        }

        /** @var Collection<EloquentTask> $tasks */
        $tasks = $this->eloquentTask
            ->newQuery()
            ->with($relations)
            ->withWhereHas('covidCase', static function (Builder $query) use ($organisationUuid): void {
                $query
                    ->withoutGlobalScope(CaseAuthScope::class)
                    ->where('organisation_uuid', $organisationUuid);
            })
            ->whereIn('uuid', $taskUuids)
            ->whereDate('updated_at', '>=', CarbonImmutable::now()->subDays(28))
            ->get($columns);

        return $tasks;
    }

    /**
     * @param Collection<int,SearchHashResult> $keyHashCollection
     *
     * @return Collection<int,Collection<int,string>>
     */
    public function getMatchingTaskUuids(Collection $keyHashCollection): Collection
    {
        assert(
            $keyHashCollection->count() <= 1000,
            new SearchHashRuntimeException('Not allowed to pass more than a 1000 SearchHashResults!'),
        );

        $query = $this->taskSearch->newQuery();

        foreach ($keyHashCollection as $hashObject) {
            $query->orWhere(static function (Builder $query) use ($hashObject): void {
                $query
                    ->where('key', $hashObject->key)
                    ->where('hash', $hashObject->hash);
            });
        }

        /** @var Collection<int,Collection<int,string>> $taskUuids */
        $taskUuids = $query
            ->get()
            ->groupBy('task_uuid')
            ->map(static fn (Collection $group) => $group->pluck('key'));

        return $taskUuids;
    }

    /**
     * @param array<int,string> $keys
     */
    public function deleteTaskSearchHashes(EloquentTask $task, array $keys): void
    {
        $task->search()
            ->whereIn('key', $keys)
            ->delete();
    }

    /**
     * @param array<int,array{key:string,hash:string}> $hashes
     *
     * @return Collection<int,TaskSearch>
     */
    public function createTaskSearchHashes(EloquentTask $task, array $hashes): Collection
    {
        return $task->search()->createMany($hashes);
    }
}
