<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Place;
use App\Models\Export\Cursor;
use App\Models\Export\Mutation;
use App\Models\Place\Cases\ListOptions as CaseListOptions;
use App\Models\Place\ListOptions;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use MinVWS\DBCO\Enum\Models\ContextCategory;

interface PlaceRepository
{
    public function getPlaceByUuid(string $placeUuid): ?Place;

    /**
     * @param array<string> $placeUuids
     *
     * @return Collection<int, Place>
     */
    public function getPlacesByUuids(array $placeUuids): Collection;

    /**
     * @param array<string> $locationUuids
     *
     * @return Collection<int, Place>
     */
    public function getPlaceByLocationUuids(array $locationUuids): Collection;

    /**
     * Search places and locations based on keyword.
     * Currently this only matches the Place descriptions in our own db.
     *
     * @return Collection<int, Place>
     */
    public function searchPlaceByKeyword(string $keyword): Collection;

    public function save(Place $place): void;

    /**
     * Find the streetname and town based on postal code and housenumber
     */
    public function lookupAddress(string $postalCode, ?string $houseNumber): ?array;

    /**
     * @param array<ContextCategory> $onlyCategories
     */
    public function searchSimilarPlaces(
        string $searchKeys,
        ListOptions $listOptions,
        string $organisationUuid,
        array $onlyCategories = [],
    ): LengthAwarePaginator;

    public function getCases(Place $place, CaseListOptions $listOptions): LengthAwarePaginator;

    public function resetCount(Place $place, CarbonImmutable $resetAt): void;

    /**
     * @param Collection<int, string> $organisationIds
     *
     * @return Collection<int, Mutation>
     */
    public function getMutatedPlacesForOrganisations(
        Collection $organisationIds,
        Cursor $cursor,
        int $limit,
    ): Collection;

    public function chunkPlaces(int $chunkSize, callable $callback): void;
}
