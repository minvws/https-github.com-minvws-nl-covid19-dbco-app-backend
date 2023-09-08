<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\CovidCaseSearch;
use App\Models\Eloquent\EloquentCase;
use App\Scopes\CaseAuthScope;
use App\Services\SearchHash\Dto\SearchHashCase;
use App\Services\SearchHash\Exception\SearchHashRuntimeException;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Pagination\Cursor;
use Illuminate\Support\Collection;
use Illuminate\Support\Enumerable;
use Illuminate\Support\LazyCollection;
use stdClass;

use function assert;
use function count;
use function usleep;

final class DbSearchHashCaseRepository implements SearchHashCaseRepository
{
    private string $covidCaseSearchTable;
    private string $eloquentCaseTable;

    public function __construct(
        private readonly EloquentCase $eloquentCase,
        private readonly CovidCaseSearch $covidCaseSearch,
    ) {
        $this->eloquentCaseTable = $eloquentCase->getTable();
        $this->covidCaseSearchTable = $covidCaseSearch->getTable();
    }

    public function getCaseByUuid(string $caseUuid, array $relations = []): ?EloquentCase
    {
        /** @var ?EloquentCase $case */
        $case = $this->eloquentCase
            ->newQuery()
            ->withoutGlobalScope(CaseAuthScope::class)
            ->with($relations)
            ->find($caseUuid);

        return $case;
    }

    public function getCasesByUuids(
        array $caseUuids,
        string $organisationUuid,
        array $relations = [],
        array $columns = ['*'],
    ): Collection {
        assert(count($caseUuids) <= 1000, new SearchHashRuntimeException('Not allowed to pass more than a 1000 caseUuids!'));

        return $this->eloquentCase
            ->newQuery()
            ->withoutGlobalScope(CaseAuthScope::class)
            ->with($relations)
            ->whereIn('uuid', $caseUuids)
            ->where('organisation_uuid', $organisationUuid)
            ->get($columns)
            ->collect();
    }

    public function getMatchingCaseUuids(Collection $keyHashCollection): Collection
    {
        assert(
            $keyHashCollection->count() <= 1000,
            new SearchHashRuntimeException('Not allowed to pass more than a 1000 SearchHashResults!'),
        );

        $query = $this->covidCaseSearch->newQuery();

        foreach ($keyHashCollection as $searchHashResult) {
            $query->orWhere(static function (Builder $query) use ($searchHashResult): void {
                $query
                    ->where('key', $searchHashResult->key)
                    ->where('hash', $searchHashResult->hash);
            });
        }

        /** @var Collection<int,Collection<int,string>> $caseUuids */
        $caseUuids = $query
            ->get()
            ->groupBy('covidcase_uuid')
            ->map(static fn (Collection $group): Collection => $group->pluck('key'));

        return $caseUuids;
    }

    public function deleteCaseSearchHashes(EloquentCase $case, array $keys): void
    {
        $case->search()
            ->whereIn('key', $keys)
            ->delete();
    }

    public function createCaseSearchHashes(EloquentCase $case, array $hashes): Collection
    {
        return $case->search()->createMany($hashes);
    }

    public function chunk(int $count, callable $callback, ?Cursor $startCursor = null, ?int $usleep = 1000): bool
    {
        return EloquentCase::query()
            ->getQuery()
            ->select([
                $this->eloquentCase->getConnection()->raw("distinct({$this->eloquentCaseTable}.uuid)"),
                "{$this->eloquentCaseTable}.updated_at",
                $this->eloquentCase->getConnection()->raw("IF({$this->covidCaseSearchTable}.covidcase_uuid IS NULL, 0, 1) AS has_hashes"),
            ])
            ->leftJoin($this->covidCaseSearchTable, "{$this->eloquentCaseTable}.uuid", '=', "{$this->covidCaseSearchTable}.covidcase_uuid")
            ->whereNested(function (Builder $q): void {
                // Unidentified cases/indexes are not allowed to be found after last change was 6 months ago:
                $q->whereNested(function (Builder $q2): void {
                    $q2->whereNull("{$this->eloquentCaseTable}.pseudo_bsn_guid");
                    $q2->whereRaw("{$this->eloquentCaseTable}.updated_at > CURRENT_DATE() - INTERVAL 6 MONTH");
                });
                $q->orWhereNotNull("{$this->eloquentCaseTable}.pseudo_bsn_guid");
            })
            ->whereNull("{$this->eloquentCaseTable}.deleted_at")
            ->orderByDesc("{$this->eloquentCaseTable}.updated_at")
            ->orderBy("{$this->eloquentCaseTable}.uuid")
            ->chunkUsingCursor(
                $count,
                static function (Enumerable $cases, int $page, ?Cursor $cursor) use ($callback, $usleep): bool {
                    assert($cases instanceof Collection);

                    /** @var LazyCollection<int,stdClass> $cases */
                    $cases = $cases->lazy();

                    $cases = $cases->map(static fn (stdClass $case): SearchHashCase
                        => new SearchHashCase(
                            uuid: $case->uuid,
                            updatedAt: $case->updated_at,
                            hasHashes: $case->has_hashes,
                        ));

                    $result = $callback($cases, $page, $cursor);

                    if ($usleep) {
                        usleep($usleep);
                    }

                    return $result;
                },
                $startCursor,
            );
    }

    /**
     * @codeCoverageIgnore
     *
     * @NOTE This cannot be tested because it is not possible to truncate a table in a transaction without an explicit
     * commit.
     */
    public function truncateCovidCaseSearch(): void
    {
        $this->covidCaseSearch->query()->truncate();
    }
}
