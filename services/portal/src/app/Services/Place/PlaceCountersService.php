<?php

declare(strict_types=1);

namespace App\Services\Place;

use App\Helpers\Config;
use App\Models\Eloquent\Place;
use App\Models\Eloquent\PlaceCounters;
use App\Repositories\ContextRepository;
use App\Repositories\MomentRepository;
use App\Repositories\PlaceCountersRepository;
use Carbon\CarbonImmutable;

class PlaceCountersService
{
    public function __construct(
        private readonly PlaceCountersRepository $placeCountersRepository,
        private readonly ContextRepository $contextRepository,
        private readonly MomentRepository $momentRepository,
    ) {
    }

    /**
     * @return array<string>
     */
    public function calculateValuesForPlace(Place $place): array
    {
        // Get the counters by place
        $placeCounters = $this->placeCountersRepository->getPlaceCountersByPlace($place);

        // Get all values that are needed
        $placeCounters->index_count = $this->getIndexCountForPlace($place);
        $placeCounters->index_count_since_reset = $this->getIndexCountSinceResetForPlace($place);
        $placeCounters->last_index_presence = $this->getLastIndexPresenceForPlace($place);

        // Save the counters
        return $this->placeCountersRepository->savePlaceCounters($placeCounters);
    }

    public function getIndexCountForPlace(Place $place): int
    {
        $indexCountRecentDays = Config::integer('misc.context.unique_index_count_recent_days');
        $indexCountDateLimit = CarbonImmutable::now()->subDays($indexCountRecentDays)->format('Y-m-d');

        return $this->contextRepository->getIndexCountByPlace($place, $indexCountDateLimit);
    }

    public function getIndexCountSinceResetForPlace(Place $place): int
    {
        return $this->contextRepository->getIndexCountSinceResetByPlace($place);
    }

    public function getLastIndexPresenceForPlace(Place $place): ?string
    {
        $lastIndexPresenceDateLimit = CarbonImmutable::now()->subDays(28)->format('Y-m-d h:i:s');

        return $this->momentRepository->getLastIndexPresenceByPlace($place, $lastIndexPresenceDateLimit);
    }

    public function resetIndexCountSinceReset(?PlaceCounters $placeCounters): void
    {
        if ($placeCounters === null) {
            return;
        }

        $this->placeCountersRepository->resetIndexCountSinceReset($placeCounters);
    }
}
