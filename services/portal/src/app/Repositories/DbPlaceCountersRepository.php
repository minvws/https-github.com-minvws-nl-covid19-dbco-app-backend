<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Place;
use App\Models\Eloquent\PlaceCounters;
use Psr\Log\LoggerInterface;

use function count;

class DbPlaceCountersRepository implements PlaceCountersRepository
{
    public function __construct(
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getPlaceCountersByPlace(Place $place): PlaceCounters
    {
        // Get counters from place
        $place->refresh();
        $placeCounters = $place->placeCounters;

        // If no counters exists, make a new record
        if ($placeCounters === null) {
            $placeCounters = new PlaceCounters();
            $placeCounters->place_uuid = $place->uuid;
        }

        return $placeCounters;
    }

    /**
     * @return array<string>
     */
    public function savePlaceCounters(PlaceCounters $placeCounters): array
    {
        // See if there are any changes made
        $changes = $placeCounters->syncChanges()->getChanges();

        // Save the counters if any changes were made
        if (count($changes) > 0) {
            $placeCounters->save();

            $this->logger->info('Saved placecounters', [
                'placeUuid' => $placeCounters->place->uuid,
            ]);
        }

        // return any changes that are made (will default to empty array if none have been made)
        return $changes;
    }

    public function resetIndexCountSinceReset(PlaceCounters $placeCounters): void
    {
        $placeCounters->update([
            'index_count_since_reset' => 0,
        ]);
    }
}
