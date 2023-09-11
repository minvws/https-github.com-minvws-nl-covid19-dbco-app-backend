<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\LocationApiUnauthenticatedException;
use App\Exceptions\PostalCodeValidationException;
use App\Helpers\PostalCodeHelper;
use App\Models\Eloquent\Context;
use App\Models\Eloquent\EloquentCase;
use App\Models\Eloquent\EloquentSituation;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\Section;
use App\Models\OrganisationType;
use App\Models\Place\Cases\ListOptions;
use App\Repositories\PlaceRepository;
use App\Repositories\SectionRepository;
use App\Repositories\ZipcodeRepository;
use App\Services\Location\Dto\Location;
use App\Services\Location\LocationService;
use App\Services\Place\PlaceCountersService;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use MinVWS\DBCO\Enum\Models\ContextCategory;
use Psr\Log\LoggerInterface;

use function array_merge;
use function array_search;
use function in_array;

readonly class PlaceService
{
    public function __construct(
        private PlaceRepository $placeRepository,
        private PlaceCountersService $placeCountersService,
        private SectionRepository $sectionRepository,
        private LocationService $locationService,
        private AuthenticationService $authenticationService,
        private LoggerInterface $logger,
        private ZipcodeRepository $zipcodeRepository,
    ) {
    }

    /**
     * @return array{'places': array<Place>, 'suggestions': array<Location>}
     *
     * @throws LocationApiUnauthenticatedException
     */
    public function searchPlace(string $searchTerms): array
    {
        $places = $this->placeRepository->searchPlaceByKeyword($searchTerms);

        /** @var array<string> $foundLocationIds */
        $foundLocationIds = $places->pluck('location_id')->filter()->toArray();
        $locationsByQuery = $this->locationService->findByQuery($searchTerms, $foundLocationIds);

        /** @var array<string> $externalLocationIds */
        $externalLocationIds = $locationsByQuery->pluck('id')->toArray();
        $locationIds = $this->placeRepository->getPlaceByLocationUuids($externalLocationIds)
            ->pluck('location_id')
            ->toArray();

        // Split the found locations into two collection
        // - One collection that holds locations that we already stored in our database
        // - One collection with the places we don't have stored yet.
        [$placeLocations, $locations] = $locationsByQuery
            ->partition(static function (Location $location) use ($locationIds) {
                // Check if the ID exists in the array. If so, remove it to shrink the collection.
                $key = array_search($location->id, $locationIds, true);
                if ($key !== false) {
                    unset($locationIds[$key]);

                    return true;
                }

                return false;
            });

        // Merge the already found places by location_id into our existing places collection
        $places = $places->merge($placeLocations->map(function (Location $location): ?Place {
            return $this->placeRepository->getPlaceByLocationUuids([$location->id])
                ->first();
        }));

        return [
            'places' => $places->all(),
            'suggestions' => $locations->map(static fn (Location $location) => $location->toResult())->values()->toArray(),
        ];
    }

    public function getPlace(string $placeUuid): ?Place
    {
        return $this->placeRepository->getPlaceByUuid($placeUuid);
    }

    public function getCases(Place $place, ListOptions $listOptions): LengthAwarePaginator
    {
        return $this->placeRepository->getCases($place, $listOptions);
    }

    /**
     * @return Collection<Section>
     */
    public function getSections(Place $place, bool $sortByIndexCount = false): Collection
    {
        $sections = $place->sections();

        if ($sortByIndexCount) {
            // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
            // @todo join against aggregated number of Cases at this location
        }

        return $sections->get();
    }

    public function addSectionToPlace(string $label, Place $place, ?Context $context = null): Section
    {
        // Make sure we're not adding a duplicate Section to this Place
        $section = $this->sectionRepository->getSectionByPlaceAndLabel($place, $label);
        if ($section === null) {
            $section = $this->sectionRepository->createsection($place, $label);
        }

        if ($context !== null) {
            $this->sectionRepository->linkContextToSection($context, $section);
        }

        return $section;
    }

    public function lookupAddress(string $postalCode, string $houseNumber): ?array
    {
        try {
            $postalCode = PostalCodeHelper::normalizeAndValidate($postalCode);
        } catch (PostalCodeValidationException $validationException) {
            return null;
        }

        $organisationUuid = $this->determineOrganisationUuid(null, $postalCode);

        $locations = $this->locationService->findForPostalCode($postalCode . ' ' . $houseNumber);

        if ($locations->isNotEmpty()) {
            return [array_merge($locations->toArray()[0], ['organisation_uuid' => $organisationUuid])];
        }

        $address = $this->placeRepository->lookupAddress($postalCode, $houseNumber);

        return $address ? [array_merge($address, ['organisation_uuid' => $organisationUuid])] : null;
    }

    public function createPlace(array $data): Place
    {
        // Create new place instance
        $place = Place::newInstanceWithVersion(Place::getSchema()->getCurrentVersion()->getVersion());

        // Set postalCode as separate variable as it is used more often
        $postalCode = $data['address']['postalCode'] ?? null;

        // General information
        $place->organisation_uuid = $data['organisationUuid'] ?? $place->organisation_uuid;
        $place->label = $data['label'];
        $place->category = $data['category'] instanceof ContextCategory
            ? $data['category']
            : ContextCategory::tryFrom($data['category']);

        // Location
        $place->postalcode = $postalCode !== null ? PostalCodeHelper::normalize((string) $postalCode) : $place->postalcode;
        $place->street = $data['address']['street'] ?? null;
        $place->housenumber = $data['address']['houseNumber'] ?? null;
        $place->housenumber_suffix = $data['address']['houseNumberSuffix'] ?? null;
        $place->town = $data['address']['town'] ?? null;
        $place->location_id = $data['id'] ?? null;

        // GGD
        $place->ggd_code = $data['ggd']['code'] ?? null;
        $place->ggd_municipality = $data['ggd']['municipality'] ?? null;
        $place->is_verified = $data['isVerified'] ?? false;

        // Postal code
        if (!isset($data['organisationUuid'])) {
            $place->organisation_uuid = $this->determineOrganisationUuid($place, $postalCode) ?? $place->organisation_uuid;
        }

        $this->placeRepository->save($place);

        return $place;
    }

    public function updatePlace(Place $place, array $data): Place
    {
        // Set postalCode as separate variable as it is used more often
        $postalCode = $data['address']['postalCode'] ?? null;

        // General information
        $place->organisation_uuid = $data['organisationUuid'] ?? $place->organisation_uuid;
        $place->label = $data['label'];
        $place->category = $data['category'] instanceof ContextCategory
            ? $data['category']
            : ContextCategory::tryFrom($data['category']);

        // Location
        $place->postalcode = $postalCode !== null ? PostalCodeHelper::normalize((string) $postalCode) : $place->postalcode;
        $place->street = $data['address']['street'] ?? null;
        $place->housenumber = $data['address']['houseNumber'] ?? null;
        $place->housenumber_suffix = $data['address']['houseNumberSuffix'] ?? null;
        $place->town = $data['address']['town'] ?? null;
        $place->location_id = $data['id'] ?? null;

        // GGD
        $place->ggd_code = $data['ggd']['code'] ?? null;
        $place->ggd_municipality = $data['ggd']['municipality'] ?? null;
        $place->is_verified = $data['isVerified'] ?? false;

        // Postal code
        if (!isset($data['organisationUuid'])) {
            $place->organisation_uuid = $this->determineOrganisationUuid($place, $postalCode) ?? $place->organisation_uuid;
        }

        $this->placeRepository->save($place);

        // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
        // TODO: Should be reverted with ticket BOOST-46
        // https://ggdcontact.atlassian.net/browse/BOOST-46
        if (isset($data['situationNumbers'])) {
            $this->saveSituationNumbers($place, $data['situationNumbers']);
        }

        return $place;
    }

    // phpcs:ignore Generic.Commenting.Todo.TaskFound -- baseline
    // TODO: Should be reverted with ticket BOOST-46
    // https://ggdcontact.atlassian.net/browse/BOOST-46
    public function saveSituationNumbers(Place $place, array $data): void
    {
        DB::transaction(static function () use ($place, $data): void {
            foreach ($data as $key => $situationData) {
                if (isset($situationData['uuid'])) {
                    /** @var EloquentSituation $eloquentSituation */
                    $eloquentSituation = EloquentSituation::findOrFail($situationData['uuid']);
                    $eloquentSituation->name = $situationData['name'] ?? '';
                    $eloquentSituation->hpzone_number = $situationData['value'] ?? '';
                    $eloquentSituation->save();

                    $data[$key] = $eloquentSituation->uuid;
                }

                if (isset($situationData['uuid'])) {
                    continue;
                }

                $eloquentSituation = new EloquentSituation();
                $eloquentSituation->name = $situationData['name'] ?? '';
                $eloquentSituation->hpzone_number = $situationData['value'] ?? '';
                $eloquentSituation->save();

                $place->situations()->attach($eloquentSituation);

                $data[$key] = $eloquentSituation->uuid;
            }

            $situations = $place->situations;
            if (!$situations) {
                return;
            }

            EloquentSituation::query()->whereIn(
                'uuid',
                $situations->filter(static function ($situation) use ($data) {
                    return !in_array($situation->uuid, $data, true);
                })->pluck('uuid'),
            )->delete();
        });
    }

    /**
     * When linking a Place to a context we set the case organisation as the owning
     * organisation of the place
     */
    public function setPlaceOrganisationFromCase(Place $place, EloquentCase $case): void
    {
        $place->organisation_uuid = $case->organisation_uuid;
        $place->save();
    }

    /**
     * Do a lookup of the postalcode in de zipcode table to determine which organisation is owner of the Place. When
     * not found we use a fallback to covidcase organisation or user organisation.
     */
    public function determineOrganisationUuid(?Place $place, ?string $postalCode): ?string
    {
        if ($postalCode !== null) {
            try {
                PostalCodeHelper::validate($postalCode);
            } catch (PostalCodeValidationException $postalCodeValidationException) {
                if ($place) {
                    $this->logger->debug('Error setting organisation uuid on Place', [
                        'placeUuid' => $place->uuid,
                        'exceptionMessage' => $postalCodeValidationException->getMessage(),
                    ]);
                }
            }

            $zipcode = $this->zipcodeRepository->findByZipcode($postalCode);

            if ($zipcode !== null) {
                return $zipcode->organisation_uuid;
            }
        }

        if ($place === null) {
            $this->logger->debug('Error using fallback organisation as Place is null');
            return null;
        }

        return $this->getFallbackOrganisationUuid($place);
    }

    public function resetCount(Place $place, CarbonImmutable $resetAt): void
    {
        $this->placeRepository->resetCount($place, $resetAt);
        $this->placeCountersService->resetIndexCountSinceReset($place->placeCounters);
    }

    public function calculatePlaceCounters(string $placeUuid): array
    {
        $place = $this->getPlace($placeUuid);

        if ($place === null) {
            throw new ModelNotFoundException();
        }

        return $this->placeCountersService->calculateValuesForPlace($place);
    }

    /**
     * Fallback mechanism to determine the organisation uuid for a Place.
     */
    private function getFallbackOrganisationUuid(Place $place): ?string
    {
        if ($place->contexts()->count()) {
            /** @var Context $firstContext */
            $firstContext = $place->contexts->first();
            return $firstContext->case->organisation_uuid;
        }

        $organisation = $this->authenticationService->getSelectedOrganisation();
        if (
            $organisation !== null
            && in_array($organisation->type, [OrganisationType::regionalGGD(), OrganisationType::demo()], true)
        ) {
            return $organisation->uuid;
        }

        $this->logger->debug('Place: No fallback organisation found. Place: %s', [
            'placeUuid' => $place->uuid,
        ]);

        return null;
    }
}
