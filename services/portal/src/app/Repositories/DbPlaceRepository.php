<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\Place;
use App\Models\Export\Cursor;
use App\Models\Export\Mutation;
use App\Models\Place\Cases\ListOptions as CaseListOptions;
use App\Models\Place\ListOptions;
use App\Scopes\CaseAuthScope;
use App\Services\Export\Helpers\ExportFetchMutationsHelper;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use Psr\Log\LoggerInterface;

use function count;
use function explode;
use function is_null;
use function preg_match;
use function sprintf;

class DbPlaceRepository implements PlaceRepository
{
    use LimitChecker;

    public const GET_CASES_DATE_DIFFERENCE_LIMIT = 28;

    public function __construct(
        private readonly ExportFetchMutationsHelper $fetchMutationsHelper,
        private readonly LoggerInterface $log,
    ) {
    }

    public function getPlaceByUuid(string $placeUuid): ?Place
    {
        return Place::query()
            ->find($placeUuid);
    }

    /**
     * @param array<string> $placeUuids
     *
     * @return Collection<int, Place>
     */
    public function getPlacesByUuids(array $placeUuids): Collection
    {
        /** @var Collection<int, Place> $place */
        $place = Place::query()
            ->find($placeUuids);

        return $place;
    }

    /**
     * @param array<string> $locationUuids
     *
     * @return Collection<int, Place>
     */
    public function getPlaceByLocationUuids(array $locationUuids): Collection
    {
        return Place::query()
            ->whereIn('location_id', $locationUuids)
            ->get();
    }

    /**
     * Search Place in our own database using a simplistic keyword search.
     * Current datamodel has not been optimized for performance or accuracy.
     *
     * @return Collection<int, Place>
     */
    public function searchPlaceByKeyword(string $keyword): Collection
    {
        return Place::query()
            ->where('label', 'like', sprintf("%%%s%%", $keyword))
            ->orWhere('street', 'like', sprintf("%%%s%%", $keyword))
            ->orWhere('postalcode', 'like', sprintf("%%%s%%", $keyword))
            ->limit(10)
            ->get();
    }

    public function save(Place $place): void
    {
        $place->save();
    }

    /**
     * @inheritDoc
     */
    public function lookupAddress(string $postalCode, ?string $houseNumber): ?array
    {
        $query = Place::select('street', 'town')->distinct();

        $query->where('postalcode', PostalCodeHelper::normalize($postalCode));

        if ($houseNumber !== null && preg_match('/(\d+)/', $houseNumber, $matches)) {
            $query->where('housenumber', $matches[0]);
        }

        $place = $query->first();
        if ($place === null) {
            return null;
        }

        return [
            'street' => $place->street,
            'town' => $place->town,
        ];
    }

    /**
     * @param array<ContextCategory> $onlyCategories
     */
    public function searchSimilarPlaces(string $searchKeys, ListOptions $listOptions, string $organisationUuid, array $onlyCategories = []): LengthAwarePaginator
    {
        $query = $this->getPlacesQuery();
        $this->joinPlaceCounters($query);

        $this->addOrganisationFilter($query, $organisationUuid);
        $this->addCategoryFilter($query, $onlyCategories);

        if (!is_null($listOptions->isVerified)) {
            $this->addFilterIsVerified($query, $listOptions->isVerified);
        }
        if (!empty($searchKeys)) {
            $this->addSearchFilter($query, $searchKeys);
        }
        $this->addViewOrderBy($query, $listOptions);

        return $query->paginate($listOptions->perPage, ['*'], 'page', $listOptions->page);
    }

    public function getCases(Place $place, CaseListOptions $listOptions): LengthAwarePaginator
    {
        $query = EloquentCase::query()
            ->withoutGlobalScope(CaseAuthScope::class)
            ->join('context', static function ($join) use ($place): void {
                $join->on('covidcase.uuid', '=', 'context.covidcase_uuid');
                $join->where('context.place_uuid', '=', $place->uuid);
            });

        // @phpstan-ignore-next-line
        $dateLimit = DB::query()
            ->fromRaw('(
                    SELECT
                          covidcase.uuid,
                          episode_start_date as episode_start_date_now,
                          DATEDIFF(episode_start_date, LAG(episode_start_date, 1, NOW()) OVER (ORDER BY episode_start_date DESC)) as difference
                    FROM covidcase
                    LEFT JOIN context ON covidcase.uuid = context.covidcase_uuid
                              WHERE context.place_uuid = ?
                    ) as dates', [$place->uuid])
            ->orderBy('dates.episode_start_date_now', 'DESC')
            ->whereNotNull('dates.difference')
            ->where('dates.difference', '<', sprintf('-%s', self::GET_CASES_DATE_DIFFERENCE_LIMIT))
            ->select('dates.episode_start_date_now')
            ->first()?->episode_start_date_now;

        if ($dateLimit !== null) {
            $query->whereDate('covidcase.episode_start_date', '>', $dateLimit);
        }

        if ($listOptions->sort) {
            $query->orderBy($listOptions->sort, $listOptions->order ?? 'asc');
        }

        $query->orderByRaw('covidcase.episode_start_date DESC');
        $query->select([
            'covidcase.*',
            'context.relationship AS context_relationship',
            'context.uuid AS context_uuid',
        ]);

        $result = $query->paginate(perPage: $listOptions->perPage, page: $listOptions->page);

        EloquentCollection::make($result->items())
            ->load([
                'contexts' => static function (HasMany $q) use ($place): void {
                    $q->where('place_uuid', $place->uuid);
                    $q->with([
                        'moments' => static function (HasMany $q2): void {
                            $q2->limit(100);
                        },
                    ]);
                },
                // phpcs:ignore Squiz.Arrays.ArrayDeclaration.NoKeySpecified
                'hospital',
            ])
            ->each(function (EloquentCase $case): void {
                $contexts = $case->contexts;

                $this->limitChecker($this->log, $contexts->first()?->moments ?? [], 100, 80);
            });

        return $result;
    }

    private function getPlacesQuery(): Builder
    {
        return DB::table('place')->selectRaw("
            place.*,
            place_counters.index_count,
            place_counters.index_count_since_reset,
            place_counters.last_index_presence,
            place_counters.updated_at as counters_updated_at
        ");
    }

    private function addOrganisationFilter(Builder $query, string $organisationUuid): void
    {
        $query->where('organisation_uuid', $organisationUuid);
    }

    private function addCategoryFilter(Builder $query, array $categories): void
    {
        if (count($categories) === 0) {
            return;
        }

        $query->where(static function ($query) use ($categories): void {
            foreach ($categories as $category) {
                $query->orWhere('place.category', $category->value);
            }
        });
    }

    private function addFilterIsVerified(Builder $query, bool $isVerified): void
    {
        $query->where('place.is_verified', (int) $isVerified);
    }

    private function addViewOrderBy(Builder $query, ListOptions $options): void
    {
        $sortOrder = $options->order ?? 'desc';

        switch ($options->sort) {
            case "indexCount":
                $query->orderBy("place_counters.index_count", $sortOrder);
                break;
            case "lastIndexPresence":
                $query->orderBy("place_counters.last_index_presence", $sortOrder);
                break;
        }

        $query->orderBy("place_counters.index_count_since_reset", $sortOrder);
        $query->orderBy("place_counters.index_count", $sortOrder);
        $query->orderBy("place_counters.last_index_presence", $sortOrder);
        $query->orderBy("place.is_verified");
        $query->orderByDesc("place.updated_at");
    }

    private function addSearchFilter(Builder $query, string $keywords): void
    {
        foreach (explode(' ', $keywords) as $keyword) {
            $query->where(static function ($query) use ($keyword): void {
                $query->orWhere('label', 'like', sprintf("%%%s%%", $keyword))
                    ->orWhere('street', 'like', sprintf("%%%s%%", $keyword))
                    ->orWhere('postalcode', 'like', sprintf("%%%s%%", $keyword))
                    ->orWhere('town', 'like', sprintf("%%%s%%", $keyword));
            });
        }
    }

    /**
     * @param Collection<int, string> $organisationIds
     *
     * @return Collection<int, Mutation>
     */
    public function getMutatedPlacesForOrganisations(
        Collection $organisationIds,
        Cursor $cursor,
        int $limit,
    ): Collection {
        return $this->fetchMutationsHelper->fetchMutations(
            'place',
            'i_place_mutation',
            'updated_at',
            null,
            $organisationIds,
            $cursor,
            $limit,
        );
    }

    private function joinPlaceCounters(Builder $query): void
    {
        $query->leftJoin('place_counters', 'place_counters.place_uuid', '=', 'place.uuid');
    }

    public function resetCount(Place $place, CarbonImmutable $resetAt): void
    {
        $place->index_count_reset_at = $resetAt;
        $place->save();
    }

    public function chunkPlaces(int $chunkSize, callable $callback): void
    {
        Place::query()
            ->orderBy('created_at')
            ->chunk($chunkSize, $callback);
    }
}
