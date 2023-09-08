<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Eloquent\Place;
use App\Models\Eloquent\PlaceCounters;

interface PlaceCountersRepository
{
    public function getPlaceCountersByPlace(Place $place): PlaceCounters;

    public function savePlaceCounters(PlaceCounters $placeCounters): array;

    public function resetIndexCountSinceReset(PlaceCounters $placeCounters): void;
}
