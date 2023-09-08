<?php

declare(strict_types=1);

namespace App\Services\Export\Helpers;

use App\Models\Export\Cursor;
use App\Models\Export\Mutation;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use stdClass;

use function assert;
use function collect;
use function sprintf;

class ExportFetchMutationsHelper
{
    private const SAFE_INTERVAL_MINUTES = 15;

    /**
     * Fetches the mutations stored in the given table.
     *
     * This code makes the following assumptions:
     * - primary key is `uuid`
     * - table contains a column `organisation_uuid` which contains the organisation the record belongs to
     *
     * @param Collection<string> $organisationIds
     *
     * @return Collection<Mutation>
     */
    public function fetchMutations(
        string $table,
        string $index,
        string $updatedAtColumn,
        ?string $deletedAtColumn,
        Collection $organisationIds,
        Cursor $cursor,
        int $limit,
    ): Collection {
        $columns = ['uuid', $updatedAtColumn];
        if ($deletedAtColumn !== null) {
            $columns[] = $deletedAtColumn;
        }

        $maxDate = CarbonImmutable::now()->subMinutes(self::SAFE_INTERVAL_MINUTES);
        if (isset($cursor->until) && $cursor->until < $maxDate) {
            $maxDate = $cursor->until;
        }

        $baseQuery = DB::query()
            ->fromRaw(sprintf('`%s` USE INDEX (`%s`)', $table, $index))
            ->select($columns)
            ->whereIn('organisation_uuid', $organisationIds)
            ->where($updatedAtColumn, '<', $maxDate)
            ->orderBy($updatedAtColumn) // NOTE: when deleting Eloquent also updates the update stamp
            ->orderBy('uuid');

        $result = collect([]);

        if ($cursor->id !== null) {
            $query = $baseQuery
                ->clone()
                ->where($updatedAtColumn, '=', $cursor->since)
                ->where('uuid', '>', $cursor->id)
                ->limit($limit);

            $result->push(...$query->get());
        }

        if ($result->count() < $limit) {
            $query = $baseQuery
                ->clone()
                ->where($updatedAtColumn, $cursor->id === null ? '>=' : '>', $cursor->since)
                ->limit($limit - $result->count());

            if ($result->count() > 0) {
                $query->whereNotIn('uuid', $result->pluck('uuid'));
            }

            $result->push(...$query->get());
        }

        return $result->map(static function ($row) use ($updatedAtColumn, $deletedAtColumn) {
            assert($row instanceof stdClass);
            $updatedAt = CarbonImmutable::parse($row->$updatedAtColumn);
            $deletedAt = isset($row->$deletedAtColumn) ? CarbonImmutable::parse($row->$deletedAtColumn) : null;
            return new Mutation($row->uuid, $updatedAt, $deletedAt);
        });
    }
}
