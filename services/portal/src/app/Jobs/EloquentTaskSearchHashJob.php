<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\SearchHashTaskRepository;
use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\EloquentTask\General\GeneralHash;
use App\Services\SearchHash\EloquentTask\PersonalDetails\PersonalDetailsHash;
use App\Services\SearchHash\SearchHasherFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use function is_null;

class EloquentTaskSearchHashJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    public function __construct(public readonly string $taskUuid)
    {
    }

    public function uniqueId(): string
    {
        return $this->taskUuid;
    }

    public function handle(
        SearchHashTaskRepository $taskRepository,
        SearchHasherFactory $searchHasherFactory,
        DatabaseManager $db,
    ): void {
        $task = $taskRepository->getTaskByUuid($this->taskUuid);

        if (is_null($task)) {
            return;
        }

        $generalHasher = $searchHasherFactory->taskGeneral(GeneralHash::fromTask($task));
        $personalDetailsHasher = $searchHasherFactory->taskPersonalDetails(PersonalDetailsHash::fromTask($task));

        /** @var array<int,string> $allKeys */
        $allKeys = $generalHasher
            ->getAllKeys()
            ->merge($personalDetailsHasher->getAllKeys())
            ->map(static fn (HashCombination $hash): string => $hash->getHashKeyName())
            ->toArray();

        /** @var array<int,array{key:string,hash:string}> $createData */
        $createData = $generalHasher
            ->getHashesByKeysThatShouldExist()
            ->merge($personalDetailsHasher->getHashesByKeysThatShouldExist())
            ->map(static fn (string $hash, string $key): array => [
                'key' => $key,
                'hash' => $hash,
            ])
            ->values()
            ->toArray();

        $db->transaction(static function () use ($task, $taskRepository, $allKeys, $createData): void {
            $taskRepository->deleteTaskSearchHashes($task, $allKeys);
            $taskRepository->createTaskSearchHashes($task, $createData);
        });
    }

    /**
     * Calculate the number of seconds to wait before retrying the job.
     */
    public function backoff(): array
    {
        return [20, 40, 60];
    }
}
