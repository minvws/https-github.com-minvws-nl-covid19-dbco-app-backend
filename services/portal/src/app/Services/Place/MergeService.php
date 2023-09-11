<?php

declare(strict_types=1);

namespace App\Services\Place;

use App\Jobs\UpdatePlaceCounters;
use App\Models\Eloquent\Place;
use App\Repositories\PlaceRepository;
use Illuminate\Support\Collection;

use function collect;
use function min;
use function sprintf;
use function strval;

class MergeService
{
    private const RELATIONS = [
        'contexts',
        'sections',
        'situations',
    ];

    private Collection $contextLabelCollection;

    public function __construct(
        private readonly PlaceRepository $placeRepository,
    ) {
        $this->contextLabelCollection = collect();
    }

    /**
     * @param array<string> $mergePlaceUuids array of place-uuids
     */
    public function handle(Place $mainPlace, array $mergePlaceUuids): Place
    {
        // Make collection from label of contexts for later use
        $this->contextLabelCollection = collect($mainPlace->contexts->pluck('label'))->map(static function ($label) {
            return strval($label); // strval each label so we can flip the collection
        });

        // Merge the relations to the main place
        $this->mergePlaces($mainPlace, $mergePlaceUuids);

        // Make sure an update on the place counters is dispatched
        UpdatePlaceCounters::dispatch($mainPlace->uuid);

        // return the main place with the new relations that have been merged
        return $mainPlace->refresh()->load(self::RELATIONS);
    }

    /**
     * @param array<string> $mergePlaceUuids array of place-uuids
     */
    private function mergePlaces(Place $mainPlace, array $mergePlaceUuids): void
    {
        // change uuid for all relations of places that are found
        $mergePlaces = $this->placeRepository->getPlacesByUuids($mergePlaceUuids);

        foreach ($mergePlaces as $placeObject) {
            $mainPlace->index_count_reset_at = min($placeObject->index_count_reset_at, $mainPlace->index_count_reset_at);

            $this->updateRelations($mainPlace, $placeObject);

            $placeObject->delete();
        }

        $mainPlace->save();
    }

    private function updateRelations(Place $mainPlace, Place $mergePlace): void
    {
        $this->updateRelationContexts($mainPlace, $mergePlace);
        $this->updateRelationSections($mainPlace, $mergePlace);
        $this->updateRelationSituations($mainPlace, $mergePlace);
    }

    private function updateRelationContexts(Place $mainPlace, Place $mergePlace): void
    {
        if ($mergePlace->contexts->count() > 0) {
            // Loop over every context
            foreach ($mergePlace->contexts as $mergePlaceContext) {
                // If label is duplicate then use another label
                if ($this->contextLabelCollection->flip()->has($mergePlaceContext->label)) {
                    $newContextLabel = sprintf('%s (%s)', $mergePlaceContext->label, $mergePlaceContext->place->label);
                    $mergePlaceContext->label = $newContextLabel;
                }
                $this->contextLabelCollection->add($mergePlaceContext->label);

                // Replace the place uuid witht the new place
                $mergePlaceContext->place_uuid = $mainPlace->uuid;

                // Save the context
                $mergePlaceContext->save();
            }
        }
    }

    private function updateRelationSituations(Place $mainPlace, Place $mergePlace): void
    {
        foreach ($mergePlace->situations as $situation) {
            if ($mainPlace->situations->pluck('hpzone_number')->contains($situation->hpzone_number)) {
                continue;
            }
            $situation->places()->save($mainPlace);
        }
    }

    private function updateRelationSections(Place $mainPlace, Place $mergePlace): void
    {
        foreach ($mergePlace->sections as $section) {
            $section->place_uuid = $mainPlace->uuid;
            $section->save();
        }
    }
}
