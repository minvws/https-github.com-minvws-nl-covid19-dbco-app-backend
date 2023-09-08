<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\PlaceListOptionsException;
use App\Exceptions\PlaceVerificationException;
use App\Models\Eloquent\Place;
use App\Models\Place\ListOptions;
use App\Repositories\PlaceRepository;
use App\Services\Place\MergeService;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use MinVWS\DBCO\Enum\Models\ContextCategory;

use function array_map;
use function array_merge;

readonly class PlaceAdminService
{
    public function __construct(
        private PlaceRepository $placeRepository,
        private AuthenticationService $authService,
        private MergeService $mergeService,
    ) {
    }

    public function searchSimilarPlaces(string $searchKeys, ListOptions $listOptions): LengthAwarePaginator
    {
        $organisationUuid = $this->authService->getRequiredSelectedOrganisation()->uuid;
        $categoryFilter = $this->getCategoryFilterFromListOptions($listOptions);

        return $this->placeRepository->searchSimilarPlaces($searchKeys, $listOptions, $organisationUuid, $categoryFilter);
    }

    public function verifyPlace(Place $place): void
    {
        if ($place->is_verified === true) {
            throw new PlaceVerificationException('Cannot verify an already verified place.');
        }

        $place->is_verified = true;
        $place->save();
    }

    /**
     * @param array<string> $placeUuids
     */
    public function verifyPlaces(array $placeUuids): void
    {
        $places = $this->placeRepository->getPlacesByUuids($placeUuids);

        foreach ($places as $place) {
            $place->is_verified = true;
            $place->save();
        }
    }

    public function unVerifyPlace(Place $place): void
    {
        if ($place->is_verified === false) {
            throw new PlaceVerificationException('Cannot unverify an unverified place.');
        }

        $place->is_verified = false;
        $place->save();
    }

    /**
     * @param array<string> $mergePlaceUuids array of place-uuids
     */
    public function mergePlace(Place $mainPlace, array $mergePlaceUuids): Place
    {
        return $this->mergeService->handle($mainPlace, $mergePlaceUuids);
    }

    /**
     * @return array<ContextCategory>
     */
    private function getCategoryFilterFromListOptions(ListOptions $listOptions): array
    {
        if ($listOptions->categoryGroup !== null) {
            if ($listOptions->view !== null && $listOptions->categoryGroup->view !== $listOptions->view) {
                throw new PlaceListOptionsException('Incompatible view and categoryGroup parameters.');
            }

            return $listOptions->categoryGroup->categories;
        }

        if ($listOptions->view === null) {
            return [];
        }

        return array_merge(...array_map(static fn ($group) => $group->categories, $listOptions->view->categoryGroups));
    }
}
