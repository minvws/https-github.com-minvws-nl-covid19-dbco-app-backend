<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Repositories\SearchHashCaseRepository;
use App\Services\SearchHash\Attribute\HashCombination;
use App\Services\SearchHash\EloquentCase\Index\IndexHash;
use App\Services\SearchHash\SearchHasherFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\DatabaseManager;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

use function is_null;

class IndexSearchHashJob implements ShouldQueue, ShouldBeUniqueUntilProcessing
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    public function __construct(public readonly string $caseUuid)
    {
    }

    public function uniqueId(): string
    {
        return $this->caseUuid;
    }

    public function handle(
        SearchHashCaseRepository $caseRepository,
        SearchHasherFactory $searchHasherFactory,
        DatabaseManager $db,
    ): void {
        $case = $caseRepository->getCaseByUuid($this->caseUuid, ['index']);

        if (is_null($case)) {
            return;
        }

        $hasher = $searchHasherFactory->covidCaseIndex(IndexHash::fromIndex($case->index));

        /** @var array<int,string> $allKeys */
        $allKeys = $hasher
            ->getAllKeys()
            ->map(static fn (HashCombination $hash): string => $hash->getHashKeyName())
            ->toArray();

        /** @var array<int,array{key:string,hash:string}> $createData */
        $createData = $hasher
            ->getHashesByKeysThatShouldExist()
            ->map(static fn (string $hash, string $key): array => [
                'key' => $key,
                'hash' => $hash,
            ])
            ->values()
            ->toArray();

        $db->transaction(static function () use ($case, $caseRepository, $allKeys, $createData): void {
            $caseRepository->deleteCaseSearchHashes($case, $allKeys);
            $caseRepository->createCaseSearchHashes($case, $createData);
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
