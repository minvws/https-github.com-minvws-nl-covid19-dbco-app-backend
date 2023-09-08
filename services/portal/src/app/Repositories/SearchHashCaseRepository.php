<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CovidCaseSearch;
use App\Models\Eloquent\EloquentCase;
use App\Services\SearchHash\Dto\SearchHashResult;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Collection;

interface SearchHashCaseRepository
{
    /**
     * @param array<array-key,string> $relations
     */
    public function getCaseByUuid(string $caseUuid, array $relations = []): ?EloquentCase;

    /**
     * @param array<array-key,string> $caseUuids
     * @param array<array-key,string> $relations
     * @param array<array-key,string> $columns
     *
     * @return Collection<int,EloquentCase>
     */
    public function getCasesByUuids(
        array $caseUuids,
        string $organisationUuid,
        array $relations = [],
        array $columns = ['*'],
    ): Collection;

    /**
     * @param Collection<array-key,SearchHashResult> $keyHashCollection
     *
     * @return Collection<int,Collection<int,string>>
     */
    public function getMatchingCaseUuids(Collection $keyHashCollection): Collection;

    /**
     * @param array<int,string> $keys
     */
    public function deleteCaseSearchHashes(EloquentCase $case, array $keys): void;

    /**
     * @param array<int,array{key:string,hash:string}> $hashes
     *
     * @return Collection<int,CovidCaseSearch>
     */
    public function createCaseSearchHashes(EloquentCase $case, array $hashes): Collection;

    public function chunk(int $count, callable $callback, ?Cursor $startCursor = null, ?int $usleep = 1000): bool;

    public function truncateCovidCaseSearch(): void;
}
