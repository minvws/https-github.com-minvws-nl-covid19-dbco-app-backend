<?php

declare(strict_types=1);

namespace App\Observers;

use App\Jobs\UpdatePlaceCounters;
use App\Models\Eloquent\Place;

class PlaceObserver
{
    public function updated(Place $place): void
    {
        UpdatePlaceCounters::dispatch($place->uuid);
    }
}
